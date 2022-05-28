<?php

namespace app\admin\model\shopro;

use think\Model;


class Banner extends Model
{

    

    

    // 表名
    protected $name = 'shopro_banner';


    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $deleteTime = false;

    // 追加属性
    protected $append = [

    ];









}
