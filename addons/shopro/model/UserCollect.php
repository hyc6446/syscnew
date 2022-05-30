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
            $bank->is_consume = $is_consume??0;//链上 资产是否销毁
            $bank->status = $status??0;//状态:0=正常,1=正在寄售,2=已售出,3=已合成,4=已赠予
            $bank->price = $price??0;//寄售价格
            $bank->save();
        } else {
            $bank = new self();
            $bank->user_id = $user_id;
            $bank->goods_id = $goods_id;
            $bank->original_price = $original_price??0;//购买价格
            $bank->asset_id = $asset_id??0;//链上 资产id
            $bank->shard_id = $shard_id??0;//链上 碎片id
            $bank->give_user_id = $give_user_id??0;//赠予人
            $bank->is_consume = 0;//链上 资产是否销毁
            $bank->owner_addr = $owner_addr??'';//资产账户地址
            $bank->querysds = $querysds??'';//资产信息json
            $bank->status = 0;//状态:0=正常,1=正在寄售,2=已售出,3=已合成,4=已赠予
            $bank->type = $type; // '获得方式:1=购买,2=合成,3=赠送,4=盲盒',
            $bank->save();
        }

        return $collect;
    }
}
