<?php

namespace addons\shopro\model;

use think\Model;

/**
 * 配置模型
 */
class News extends Model
{

    // 表名,不含前缀
    protected $name = 'shopro_news';
    // 追加属性
    protected $append = [
    ];
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $deleteTime = 'deletetime';
    protected $dateFormat = 'Y-m-d H:i';



    public function getContentAttr($value, $data)
    {
        $content = $data['content'];
        $content = str_replace("<img src=\"/uploads", "<img style=\"width: 100%;!important\" src=\"" . cdnurl("/uploads", true), $content);
        $content = str_replace("<video src=\"/uploads", "<video style=\"width: 100%;!important\" src=\"" . cdnurl("/uploads", true), $content);
        return $content;
    }

}
