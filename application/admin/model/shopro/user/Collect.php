<?php

namespace app\admin\model\shopro\user;

use addons\shopro\model\Goods;
use think\Model;


class Collect extends Model
{
    // 表名
    protected $name = 'shopro_user_collect';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';


    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = false;
    protected $deleteTime = false;


    // 追加属性
    protected $append = [
        'status_text','type_text','is_consume_text'
    ];



    public function getStatusList()
    {
        return ['0' => __('Status 0'), '1' => __('Status 1'), '2' => __('Status 2'), '3' => __('Status 3'), '4' => __('Status 4')];
    }
    public function getTypeList()
    {
        return ['1' => __('Type 1'), '2' => __('Type 2'), '3' => __('Type 3'), '4' => __('Type 4'), '5' => __('Type 5')];
    }
    public function getIsConsumeList()
    {
        return ['1' => __('Is_consume 1'), '0' => __('Is_consume 0')];
    }
    public function getStatusTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['status']) ? $data['status'] : '');
        $list = $this->getStatusList();
        return isset($list[$value]) ? $list[$value] : '';
    }
    public function getTypeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['from_type']) ? $data['from_type'] : '');
        $list = $this->getTypeList();
        return isset($list[$value]) ? $list[$value] : '';
    }

    public function getIsConsumeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['is_consume']) ? $data['is_consume'] : '');
        $list = $this->getIsConsumeList();
        return isset($list[$value]) ? $list[$value] : '';
    }

    public function user()
    {
        return $this->belongsTo('\app\admin\model\shopro\user\User', 'user_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }
    public function giveUser()
    {
        return $this->belongsTo('\app\admin\model\shopro\user\User', 'give_user_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }
    public function goods()
    {
        return $this->belongsTo(\app\admin\model\shopro\goods\Goods::class, 'goods_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }
}
