<?php

namespace addons\shopro\model;

use think\Model;

/**
 * 商品藏品标签模型
 */
class GoodsDing extends Model
{

    // 表名,不含前缀
    protected $name = 'shopro_goods_ding';
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = false;
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $deleteTime = 'deletetime';

    public static function ding($params)
    {
        $user = User::info();
        $data = ['user_id'=>$user->id,'ding_time'=>$params['ding_time']];
        if ($params['status']==0){
            //取消
             self::where($data)->delete();
            return false;
        }
         self::create($data);
        return true;
    }
}
