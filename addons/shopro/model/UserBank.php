<?php

namespace addons\shopro\model;

use think\Model;
use addons\shopro\exception\Exception;
use think\Db;
use traits\model\SoftDelete;
use Yansongda\Pay\Gateways\Alipay;

/**
 * 钱包
 */
class UserBank extends Model
{
    use SoftDelete;

    // 表名,不含前缀
    protected $name = 'shopro_user_bank';
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $deleteTime = 'deletetime';


    // 追加属性
    protected $append = [];


    // 提现账户详情
    public static function info($type,$code, $encryptCardNo = true)
    {
        $user = User::info();
        $bank = null;
        if ($type == 'wechat') {
            $platform = request()->header('platform');
//            $userOauth = \addons\shopro\model\UserOauth::get([
//                'user_id' => $user->id,
//                'platform' => $platform
//            ]);
//            if ($userOauth) {
//                $bank = [
//                    'real_name' => $userOauth->nickname,
//                    'bank_name' => '微信用户',
//                    'card_no' => $userOauth->openid
//                ];
//            }

            //获取用户授权
           $user =  self::getUserInfo($code,$platform);
           if($user){
               $bank = [
                    'real_name' => $user->nickname,
                    'bank_name' => '微信用户',
                    'card_no' => $user->openid
                ];
           }

        } else {
            $bank = self::where(['user_id' => $user->id, 'type' => $type])->find();
        }
        
        if(!$bank) {
            throw \Exception('请完善您的账户信息');
        }
        if ($encryptCardNo) {
            $bank = self::encryptCardNo($bank, $type);
        }
        return $bank;
    }


    /**
     * 微信 授权获取用户信息
     * @param $code
     * @param $platform
     * @return bool|mixed
     * @throws \think\exception\DbException
     */
    private static function getUserInfo($code,$platform)
    {
        $platformConfig = json_decode(\addons\shopro\model\Config::get(['name' => $platform])->value, true);
        $appid = $platformConfig['app_id'];
        $appsecret = $platformConfig['secret'];

        // 通过code获取access_token
        $get_token_url ="https://api.weixin.qq.com/sns/oauth2/access_token?appid=". $appid ."&secret=". $appsecret ."&code={$code}&grant_type=authorization_code";
        $token_info = \fast\Http::get($get_token_url);
        $token_info = json_decode($token_info,true);
        if(isset($token_info['errcode'])){
            throw \Exception($token_info['errmsg'],$token_info['errcode']);
            return false;
        }
        // 通过access_token和openid获取用户信息
        $get_userinfo_url ='https://api.weixin.qq.com/sns/userinfo?access_token='. $token_info['access_token'].'&openid='. $token_info['openid'].'&lang=zh_CN';
        $userinfo = \fast\Http::get($get_userinfo_url);
        $userinfo = json_decode($userinfo,true);
        if(isset($userinfo['errcode'])){
            throw \Exception($userinfo['errmsg'],$userinfo['errcode']);
            return false;
        }
        return $userinfo;
    }

    /**
     * 获取支付宝的用户信息
     */
    private static function getAliPayInfo(){



    }


    private static function encryptCardNo($bank, $platform)
    {
        switch ($platform) {
            case 'wechat':
                //加密openid
                $bank['card_no'] = substr_replace($bank['card_no'], '****', 3, 20);
                break;
            case 'bank':
                // 加密银行卡号
                $bank['card_no'] = substr_replace($bank['card_no'], '****', 3, 12);
                break;
            case 'alipay':
                // 加密支付宝账号
                $bank['card_no'] = substr_replace($bank['card_no'], '****', 3, 10);
                break;
        }
        return $bank;
    }
    // 编辑提现账户
    public static function edit($params)
    {
        $user = User::info();

        extract($params);

        $bank = self::where(['user_id' => $user->id, 'type' => $type])->find();

        if ($bank) {
            $bank->real_name = $real_name;
            $bank->bank_name = $bank_name;
            $bank->card_no = $card_no;
            $bank['type'] = $type;
            $bank->save();
        } else {
            $bank = new self();
            $bank->user_id = $user->id;
            $bank['type'] = $type;
            $bank->real_name = $real_name;
            $bank->bank_name = $bank_name;
            $bank->card_no = $card_no;
            $bank->save();
        }

        return $bank;
    }
}
