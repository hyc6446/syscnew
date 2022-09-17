<?php

namespace addons\shopro\model;

use think\Db;
use think\Model;
use traits\model\SoftDelete;
use addons\shopro\model\UserBank;
use addons\shopro\exception\Exception;
use think\Log;

/**
 * 钱包
 */
class UserWalletApply extends Model
{
    use SoftDelete;

    // 表名,不含前缀
    protected $name = 'shopro_user_wallet_apply';
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $deleteTime = false;

    protected $hidden = ['actual_money', 'log', 'payment_json', 'updatetime'];

    // 追加属性
    protected $append = [
        'status_text',
        'apply_type_text',
    ];


    /**
     * 获取提现单号
     *
     * @param int $user_id
     * @return string
     */
    public static function getSn($user_id)
    {
        $rand = $user_id < 9999 ? mt_rand(100000, 99999999) : mt_rand(100, 99999);
        $order_sn = date('Yhis') . $rand;

        $id = str_pad($user_id, (24 - strlen($order_sn)), '0', STR_PAD_BOTH);

        return 'W' . $order_sn . $id;
    }

    // 提现记录
    public static function getList()
    {
        $user = User::info();

        $walletApplys = self::where(['user_id' => $user->id])->order('id desc')->paginate(10);

        return $walletApplys;
    }

    /**
     * 申请提现
     *
     * @param int $type 提现方式 wechat|alipay|bank
     * @param int $money 提现金额
     */
    public static function apply($type, $money)
    {
        $user = User::info();
        // Log::info($user);
        $config = self::getWithdrawConfig();
        if (!in_array($type, $config['methods'])) {
            throw \Exception('暂不支持该提现方式');
        }

        $min = round(floatval($config['min']), 2);
        $max = round(floatval($config['max']), 2);
        $service_fee = round(floatval($config['service_fee']), 3);      // 三位小数

        // 检查最小提现金额
        if ($money < $min || $money <= 0) {
            throw \Exception('提现金额不能少于 ' . $min . '元');
        }
        // 检查最大提现金额
        if ($max && $money > $max) {
            throw \Exception('提现金额不能大于 ' . $max . '元');
        }

        // 计算手续费
        // $charge = $money * $service_fee;
        $charge = 0;
        if ($user->money < $charge + $money) {
            throw \Exception('可提现余额不足');
        }

        // 检查每日最大提现次数
        if (isset($config['perday_num']) && $config['perday_num'] > 0) {
            $num = self::where(['user_id' => $user->id, 'createtime' => ['egt', strtotime(date("Y-m-d", time()))]])->count();
            if ($num >= $config['perday_num']) {
                throw \Exception('每日提现次数不能大于 ' . $config['perday_num'] . '次');
            }
        }

        // 检查每日最大提现金额
        if (isset($config['perday_amount']) && $config['perday_num'] > 0) {
            $amount = self::where(['user_id' => $user->id, 'createtime' => ['egt', strtotime(date("Y-m-d", time()))]])->sum('money');
            if ($amount >= $config['perday_amount']) {
                throw \Exception('每日提现金额不能大于 ' . $config['perday_amount'] . '元');
            }
        }

        // 检查提现账户信息
        $bank = \addons\shopro\model\UserBank::info($type, false);

        // 添加提现记录
        $platform = request()->header('platform');

        $apply = new self();
        $apply->apply_sn = self::getSn($user->id);
        $apply->user_id = $user->id;
        $apply->money = $money;
        $apply->charge_money = $charge;
        $apply->service_fee = $service_fee;
        $apply->apply_type = $type;
        $apply->platform = $platform;
        switch ($type) {
            case 'wechat':
                $applyInfo = [
                    '微信用户' => $bank['real_name'],
                    '微信ID'  => $bank['card_no'],
                ];
                break;
            case 'alipay':
                $applyInfo = [
                    '真实姓名' => $bank['real_name'],
                    '支付宝账户' => $bank['card_no']
                ];
                break;
            case 'bank':
                $applyInfo = [
                    '真实姓名' => $bank['real_name'],
                    '开户行' => $bank['bank_name'],
                    '银行卡号' => $bank['card_no']
                ];
                break;
        }
        if (!isset($applyInfo)) {
            throw \Exception('您的提现信息有误');
        }
        $apply->apply_info = $applyInfo;

        $apply->status = 0;
        $apply->save();
        self::handleLog($apply, '用户发起提现申请');
        // 扣除用户余额
        User::money(- ($money + $charge), $user->id, 'cash', $apply->id);

        // 检查是否执行自动打款
        $autoCheck = false;
        if ($type !== 'bank' && $config['wechat_alipay_auto']) {
            $autoCheck = true;
        }

        if ($autoCheck) {
            $apply = self::handleAgree($apply);
            $apply = self::handleWithdraw($apply);
        }

        return $apply;
    }

    public static function handleLog($apply, $oper_info)
    {
        $log = $apply->log;
        $oper = \addons\shopro\library\Oper::set();
        $log[] = [
            'oper_type' => $oper['oper_type'],
            'oper_id' => $oper['oper_id'],
            'oper_info' => $oper_info,
            'oper_time' => time()
        ];
        $apply->log = $log;
        $apply->save();
        return $apply;
    }



    // 同意
    public static function handleAgree($apply)
    {
        if ($apply->status != 0) {
            throw \Exception('请勿重复操作');
        }
        $apply->status = 1;
        $apply->save();
        return self::handleLog($apply, '同意提现申请');
    }

    // 处理打款
    public static function handleWithdraw($apply)
    {
        $withDrawStatus = false;
        if ($apply->status != 1) {
            throw \Exception('请勿重复操作');
        }
        if ($apply->apply_type !== 'bank') {
            $withDrawStatus = self::handleTransfer($apply);
        } else {
            $withDrawStatus = true;
        }
        if ($withDrawStatus) {
            $datalist = [];
            $withDraw = UserBank::where(["user_id" => $apply->user_id])->field("card_no,real_name")->find();
            $body['accNo'] = $withDraw['card_no'];
            $body['accName'] = $withDraw['real_name'];
            $body['tranAmt'] = $apply->money;
            //调用杉德接口
            $datalist['body'] = array(
                'version'     => '01',
                'productId'   => "00000004",
                'tranTime'     => date('YmdHis', time()),
                'orderCode'     => date('YmdHis', time()),
                'tranAmt' => sprintf("%012d", $body['tranAmt'] * 100),
                'currencyCode'  => "156",
                'accAttr'  => "0",
                'accType'  => "4",
                'accNo'  => $body['accNo'],
                'accName'  => $body['accName'],
                'remark'         => "",
            );

            $sf = (new self());

            $AESKey = $sf->aes_generate(16);
            $pubKey = $sf->publicKey();
            $priKey = (new self())->privateKey();
            $encryptKey = $sf->RSAEncryptByPub($AESKey, $pubKey);
            // step3: 使用AESKey加密报文
            $encryptData = $sf->AESEncrypt($datalist['body'], $AESKey);
            // step4: 使用私钥签名报文
            $postData = array(
                'encryptKey' => $encryptKey,
                'encryptData' => $encryptData,
                'transCode' => 'RTPM',
                'accessType' => '0',
                'merId' => '6888805045868',
                'sign'     => $sf->sign($datalist['body']),
            );
            $datalist['head'] = $postData;
            // $postData = $sf->postData($body);
            $url = "https://caspay.sandpay.com.cn/agent-main/openapi/agentpay";
            $ret = $sf->post_wx($url, $postData);
            Log::info("提现回执", json_encode($ret));
            parse_str($ret, $arr);
            // step7: 使用私钥解密AESKey
            $decryptAESKey = $sf->RSADecryptByPri($arr['encryptKey'], $priKey);
            // step8: 使用解密后的AESKey解密报文
            $decryptPlainText = $sf->AESDecrypt($arr['encryptData'], $decryptAESKey);
            // step9: 使用公钥验签报文
            $sf->verify($decryptPlainText, $arr['sign'], $pubKey);
            $plain = json_decode($decryptPlainText, true);
            if ($plain['respCode'] == "000000") {
                $apply->status = 2;
                $apply->actual_money = $apply->money;
                $apply->save();
                return self::handleLog($apply, '操作成功');
            } else {
                throw \Exception($plain['respDesc']);
            }
        }
        return $apply;
    }

    // 拒绝
    public static function handleReject($apply, $rejectInfo)
    {
        if ($apply->status != 0 && $apply->status != 1) {
            throw \Exception('请勿重复操作');
        }
        $apply->status = -1;
        $apply->save();
        User::money($apply->money + $apply->charge_money, $apply->user_id, 'cash_error', $apply->id);
        return self::handleLog($apply, '拒绝:' . $rejectInfo);
    }

    // 企业付款提现
    private static function handleTransfer($apply)
    {
        $type = $apply->apply_type;
        $platform = $apply->platform;
        // 1.企业自动付款
        $pay = new \addons\shopro\library\PayService($type, $platform, '', 'transfer');

        // 2.组装数据
        try {
            if ($type == 'wechat') {
                $payload = [
                    'partner_trade_no' => $apply->apply_sn,
                    'openid' => $apply->apply_info['微信ID'],
                    'check_name' => 'NO_CHECK',
                    'amount' => $apply->money,
                    'desc' => "用户[{$apply->apply_info['微信用户']}]提现"
                ];
            } elseif ($type == 'alipay') {
                $payload = [
                    'out_biz_no' => $apply->apply_sn,
                    'trans_amount' => $apply->money,
                    'product_code' => 'TRANS_ACCOUNT_NO_PWD',
                    'biz_scene' => 'DIRECT_TRANSFER',
                    'order_title' => '用户提现',
                    'remark' => '用户提现',
                    'payee_info' => [
                        //                        'identity' => $apply->apply_info['支付宝账户'],
                        'identity' => '2088812987759790',
                        'identity_type' => 'ALIPAY_USER_ID',
                        //                        'name' => $apply->apply_info['真实姓名'],
                    ]
                ];
            }
        } catch (\Exception $e) {
            throw \Exception('提现信息不正确');
        }


        // 3.发起付款 
        try {
            list($code, $response) = $pay->transfer($payload);

            if ($code === 1) {
                $apply->payment_json = json_encode($response, JSON_UNESCAPED_UNICODE);
                $apply->save();
                return true;
            }
        } catch (\Exception $e) {
            \think\Log::error('提现失败：' . ' 行号：' . $e->getLine() . '文件：' . $e->getFile() . '错误信息：' . $e->getMessage());
            throw \Exception($e->getMessage());
        }
        return false;
    }


    /**
     * 提现类型列表
     */
    public function getApplyTypeList()
    {
        return ['bank' => '银行卡', 'wechat' => '微信零钱', 'alipay' => '支付宝账户'];
    }


    /**
     * 提现类型中文
     */
    public function getApplyTypeTextAttr($value, $data)
    {
        $value = isset($data['apply_type']) ? $data['apply_type'] : '';
        $list = $this->getApplyTypeList();
        return isset($list[$value]) ? $list[$value] : '';
    }

    /**
     * 提现信息
     */
    public function getApplyInfoAttr($value, $data)
    {
        $value = isset($data['apply_info']) ? $data['apply_info'] : $value;
        return json_decode($value, true);
    }

    /**
     * 提现信息 格式转换
     */
    public function setApplyInfoAttr($value, $data)
    {
        $value = isset($data['apply_info']) ? $data['apply_info'] : $value;
        $applyInfo = json_encode($value, JSON_UNESCAPED_UNICODE);
        return $applyInfo;
    }

    public function getStatusTextAttr($value, $data)
    {
        switch ($data['status']) {
            case 0:
                $status_name = '审核中';
                break;
            case 1:
                $status_name = '处理中';
                break;
            case 2:
                $status_name = '已处理';
                break;
            case -1:
                $status_name = '已拒绝';
                break;
            default:
                $status_name = '';
        }

        return $status_name;
    }


    public static function getWithdrawConfig()
    {
        $config = \addons\shopro\model\Config::where('name', 'withdraw')->find();
        return json_decode($config['value'], true);
    }

    /**
     * 获取日志字段数组
     */
    public function getLogAttr($value, $data)
    {
        $value = array_filter((array)json_decode($value, true));
        return (array)$value;
    }

    /**
     * 设置日志字段
     * @param mixed $value
     * @return string
     */
    public function setLogAttr($value)
    {
        $value = is_object($value) || is_array($value) ? json_encode($value, JSON_UNESCAPED_UNICODE) : $value;
        return $value;
    }


    //以post方式提交xml到对应的接口url
    private function post_wx($url, $post_data, $header = [])
    {
        $post_data = http_build_query($post_data);
        try {

            $ch = curl_init(); //初始化curl
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            //正式环境时解开注释
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            $data = curl_exec($ch); //运行curl
            curl_close($ch);

            if (!$data) {
                throw new \Exception('请求出错');
            }

            return $data;
        } catch (\Exception $e) {
            throw $e;
        }
    }
    // 私钥加签
    protected function sign($plainText)
    {
        $plainText = json_encode($plainText);
        try {
            $resource = openssl_pkey_get_private($this->privateKey());
            $result   = openssl_sign($plainText, $sign, $resource);
            openssl_free_key($resource);
            if (!$result) throw new \Exception('sign error');
            return base64_encode($sign);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    // 公钥验签
    public function verify($plainText, $sign)
    {
        $resource = openssl_pkey_get_public($this->publicKey());
        $result   = openssl_verify($plainText, base64_decode($sign), $resource);
        openssl_free_key($resource);

        if (!$result) {
            throw new \Exception('签名验证未通过,plainText:' . $plainText . '。sign:' . $sign);
        }
        return $result;
    }

    // 公钥
    private function publicKey()
    {
        try {
            $file = file_get_contents("/www/wwwroot/nft/addons/shopro/cert/sand.cer");
            if (!$file) {
                throw new \Exception('getPublicKey::file_get_contents ERROR 公钥文件读取有误,config文件夹中进行修改');
            }
            $cert   = chunk_split(base64_encode($file), 64, "\n");
            $cert   = "-----BEGIN CERTIFICATE-----\n" . $cert . "-----END CERTIFICATE-----\n";
            $res    = openssl_pkey_get_public($cert);
            $detail = openssl_pkey_get_details($res);
            openssl_free_key($res);
            if (!$detail) {
                throw new \Exception('getPublicKey::openssl_pkey_get_details ERROR 公钥文件解析有误');
            }
            return $detail['key'];
        } catch (\Exception $e) {
            throw $e;
        }
    }

    // 私钥
    private function privateKey()
    {
        try {
            $file = file_get_contents("/www/wwwroot/nft/addons/shopro/cert/mid_new.pfx");
            if (!$file) {
                throw new \Exception('getPrivateKey::file_get_contents 私钥文件读取有误,config文件夹中进行修改');
            }
            if (!openssl_pkcs12_read($file, $cert, "xiangyi888")) {
                throw new \Exception('getPrivateKey::openssl_pkcs12_read ERROR 私钥密码错误，config文件夹中进行修改');
            }
            return $cert['pkey'];
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * 公钥加密AESKey
     * @param $plainText
     * @param $puk
     * @return string
     * @throws Exception
     */
    function RSAEncryptByPub($plainText, $puk)
    {
        if (!openssl_public_encrypt($plainText, $cipherText, $puk, OPENSSL_PKCS1_PADDING)) {
            throw new \Exception('AESKey 加密错误');
        }

        return base64_encode($cipherText);
    }

    /**
     * 私钥解密AESKey
     * @param $cipherText
     * @param $prk
     * @return string
     * @throws Exception
     */
    function RSADecryptByPri($cipherText, $prk)
    {
        if (!openssl_private_decrypt(base64_decode($cipherText), $plainText, $prk, OPENSSL_PKCS1_PADDING)) {
            throw new \Exception('AESKey 解密错误');
        }

        return (string)$plainText;
    }

    /**
     * AES加密
     * @param $plainText
     * @param $key
     * @return string
     * @throws \Exception
     */
    function AESEncrypt($plainText, $key)
    {
        $plainText = json_encode($plainText);
        $result = openssl_encrypt($plainText, 'AES-128-ECB', $key, 1);

        if (!$result) {
            throw new \Exception('报文加密错误');
        }

        return base64_encode($result);
    }

    /**
     * AES解密
     * @param $cipherText
     * @param $key
     * @return string
     * @throws \Exception
     */
    function AESDecrypt($cipherText, $key)
    {
        $result = openssl_decrypt(base64_decode($cipherText), 'AES-128-ECB', $key, 1);

        if (!$result) {
            throw new \Exception('报文解密错误', 2003);
        }

        return $result;
    }

    /**
     * 生成AESKey
     * @param $size
     * @return string
     */
    function aes_generate($size)
    {
        $str = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        $arr = array();
        for ($i = 0; $i < $size; $i++) {
            $arr[] = $str[mt_rand(0, 61)];
        }

        return implode('', $arr);
    }
}
