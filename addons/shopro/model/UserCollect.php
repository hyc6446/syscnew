<?php

namespace addons\shopro\model;

use think\Model;

/**
 * 用户藏品
 * Class UserCollect
 * @package addons\shopro\model
 */
class UserCollect extends Model
{

    // 表名
    protected $name = 'shopro_user_collect';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'status_time';

    // 追加属性
    protected $append = [];

    public static function edit($params)
    {

        extract($params);

        if (!$user_id || !$goods_id) return false;
        $collect = self::where(['user_id' => $user_id, 'goods_id' => $goods_id])->find();

        if ($collect) {
            $collect->is_consume = $is_consume??0;//链上 资产是否销毁
            $collect->status = $status??0;//状态:0=正常,1=正在寄售,2=已售出,3=已合成,4=已赠予
            $collect->price = $price??0;//寄售价格
            $collect->save();
        } else {
            $collect = new self();
            $collect->user_id = $user_id;
            $collect->goods_id = $goods_id;
            $collect->original_price = $original_price??0;//购买价格
            $collect->asset_id = $asset_id??0;//链上 资产id
            $collect->shard_id = $shard_id??0;//链上 碎片id
            $collect->give_user_id = $give_user_id??0;//赠予人
            $collect->is_consume = 0;//链上 资产是否销毁
            $collect->owner_addr = $owner_addr??'';//资产账户地址
            $collect->querysds = $querysds??'';//资产信息json
            $collect->status = 0;//状态:0=正常,1=正在寄售,2=已售出,3=已合成,4=已赠予
            $collect->type = $type; // '获得方式:1=购买,2=合成,3=赠送,4=盲盒',
            $collect->save();
        }

        return $collect;
    }
}
