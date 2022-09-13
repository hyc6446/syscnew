<?php

namespace app\admin\model;

use nft\ChainAccount;
use think\Model;
use think\Session;

class Admin extends Model
{

    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';

    /**
     * 重置用户密码
     * @author baiyouwen
     */
    public function resetPassword($uid, $NewPassword)
    {
        $passwd = $this->encryptPassword($NewPassword);
        $ret = $this->where(['id' => $uid])->update(['password' => $passwd]);
        return $ret;
    }

    // 密码加密
    protected function encryptPassword($password, $salt = '', $encrypt = 'md5')
    {
        return $encrypt($password . $salt);
    }


    public function setAdmin($id)
    {
        $admin = Admin::get($id);
        //查询
        if($admin->wcl_status==0){
            $txRes = (new ChainAccount())->QueryChainAccount($admin->operation_id);
            if (!empty($txRes['data']) &&!empty($txRes['data']['accounts'][0])){
                $data = $txRes['data']['accounts'][0];
                extract($data);
                $admin->gas = $gas??'';
                $admin->business = $business??'';
                $admin->wcl_status = $status??'';
                $admin->save();
            }
        }
        return $admin;
    }
}
