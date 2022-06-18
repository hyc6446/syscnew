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

        $bank = self::where(['user_id' => $user->id, 'type' => $type])->find();
        
        if(!$bank) {
            throw \Exception('请完善您的账户信息');
        }
        if ($encryptCardNo) {
            $bank = self::encryptCardNo($bank, $type);
        }
        return $bank;
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
    public static function edit($userInfo,$type)
    {
        $user = User::info();

        $bank = self::where(['user_id' => $user->id, 'type' => $type])->find();

        // 整理数据
        $real_name = '';
        $bank_name ='';
        $card_no = '';
        if($type=='wechat'){
            $real_name = $userInfo['nickname'];
            $bank_name = $userInfo['bank_name'];
            $card_no = $userInfo['open_id'];
        }else{
            $real_name = $userInfo['nickname'];
            $bank_name = $userInfo['bank_name'];
            $card_no = $userInfo['user_id'];
        }


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
