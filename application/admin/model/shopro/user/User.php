<?php

namespace app\admin\model\shopro\user;


use app\admin\model\shopro\user\MoneyLog;
use app\admin\model\shopro\user\ScoreLog;
use addons\shopro\library\notify\Notifiable;
use nft\ChainAccount;
use think\Model;
use think\Db;

class User extends Model
{
    use Notifiable;
    // 表名
    protected $name = 'user';
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    // 追加属性
    protected $append = [
        'wcl_status_text'
    ];
    public function getWclStatusList()
    {
        return ['未授权','已授权'];
    }
    public function getWclStatusTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['wcl_status']) ? $data['wcl_status'] : '');
        $list = $this->getWclStatusList();
        return isset($list[$value]) ? $list[$value] : '';
    }

    public function getGenderList()
    {
        return ['1' => __('Male'), '0' => __('Female')];
    }

    public function getStatusList()
    {
        return ['normal' => __('Normal'), 'hidden' => __('Hidden')];
    }

    public function group()
    {
        return $this->belongsTo('Group', 'group_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }
    
    public function agent()
    {
        return $this->hasOne(\app\admin\model\shopro\commission\Agent::class, 'user_id', 'id');
    }

        /**
     * 获取验证字段数组值
     * @param   string $value
     * @param   array  $data
     * @return  object
     */
    public function getVerificationAttr($value, $data)
    {
        $value = array_filter((array)json_decode($value, true));
        $value = array_merge(['email' => 0, 'mobile' => 0], $value);
        return (object)$value;
    }

    /**
     * 设置验证字段
     * @param mixed $value
     * @return string
     */
    public function setVerificationAttr($value)
    {
        $value = is_object($value) || is_array($value) ? json_encode($value) : $value;
        return $value;
    }

    public function setWclUser($id)
    {
        $user = \app\common\model\User::get($id);
        //查询
        if($user->wcl_status==0){
            $txRes = (new ChainAccount())->QueryChainAccount($user->operation_id);
            if (!empty($txRes['data']) &&!empty($txRes['data']['accounts'][0])){
                $data = $txRes['data']['accounts'][0];
                extract($data);
                $user->gas = $gas??'';
                $user->business = $business??'';
                $user->wcl_status = $status??'';
                $user->save();
            }
        }
        return $user;
    }
    
}
