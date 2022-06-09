<?php


namespace addons\shopro\model;


use think\Model;
use traits\model\SoftDelete;

class BoxOrder extends Model
{
    use SoftDelete;

    // 表名
    protected $name = 'shopro_box_order';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';
    protected $deleteTime = 'delete_time';

    // 追加属性
    protected $append = [];

    protected static function init()
    {
        self::afterInsert(function ($order) {
            $order->out_trade_no = $order->out_trade_no . str_pad(substr($order->id, -2), 2, 0, STR_PAD_LEFT);
            $order->save();
            return true;
        });
    }

}