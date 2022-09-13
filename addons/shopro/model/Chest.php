<?php

namespace addons\shopro\model;

use think\Model;
use addons\shopro\exception\Exception;
use think\Db;
use think\Queue;

/**
 * 宝箱模型
 */
class Chest extends Model
{

    // 表名,不含前缀
    protected $name = 'shopro_chest';

    // 追加属性
    protected $append = [
    ];

}

