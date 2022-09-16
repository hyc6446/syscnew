<?php

namespace addons\shopro\model;

use think\Model;
use think\Log;
use think\Db;
use addons\shopro\exception\Exception;

/**
 * 商品藏品标签模型
 */
class PriorityBuy extends Model
{

    // 表名,不含前缀
    protected $name = 'shopro_priority_buy';
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'snapshot_time';
    protected $updateTime = 'updatetime';
    protected $deleteTime = 'deletetime';
    protected $hidden = [];



    // 追加属性
    protected $append = [];

    public static function buyerList($uid)
    {
        $buyer = self::field('user_id,buy_num,beforehand_time,status')->where(['user_id' => $uid, 'status' => 1])->find();
        Log::info("buyer", json_encode($buyer));
        return $buyer;
    }
    // 修改优先购次数
    public static function editNum($uid, $num)
    {
        try {
            $user = self::get(['user_id' => $uid, 'status' => 1]);
            $buy_num = $user['buy_num'] - $num;
            $status = 1;
            if ($buy_num == 0) {
                $status = 0;
            }
            self::update(['buy_num' => $buy_num, 'status' => $status], ['user_id' => $uid, 'status' => 1]);
            return true;
        } catch (\Exception $e) {
            new Exception('优先购购买次数处理失败');
        }
    }
    // 清除优先购买权
    public static function deleteState($uid)
    {
        try {
            self::update(['buy_num' => 0, 'status' => 0], ['user_id' => $uid, 'status' => 1]);
            return true;
        } catch (\Exception $e) {
            new Exception('优先购购买权清除失败');
        }
    }


    public function getImageAttr($value, $data)
    {
        if (!empty($value)) return cdnurl($value, true);
    }
}
