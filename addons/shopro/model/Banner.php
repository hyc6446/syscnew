<?php

namespace addons\shopro\model;

use think\Model;

/**
 * 配置模型
 */
class Banner extends Model
{

    // 表名,不含前缀
    protected $name = 'shopro_banner';
    // 追加属性
    protected $append = [
    ];
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $dateFormat = 'Y-m-d H:i';



    public function getImageAttr($value, $data)
    {
        if (!empty($value)) return cdnurl($value, true);
    }
}
