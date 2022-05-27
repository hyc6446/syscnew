<?php

namespace addons\shopro\model;

use think\Model;

/**
 * 商品藏品标签模型
 */
class GoodsBrand extends Model
{

    // 表名,不含前缀
    protected $name = 'shopro_goods_brand';
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $deleteTime = 'deletetime';

    protected $hidden = ['createtime', 'updatetime', 'deletetime','description'];



    // 追加属性
    protected $append = [

    ];



    public function getImageAttr($value, $data)
    {
        if (!empty($value)) return cdnurl($value, true);

    }



}
