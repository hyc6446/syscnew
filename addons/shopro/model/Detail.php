<?php


namespace addons\shopro\model;


use think\Model;
use traits\model\SoftDelete;

class Detail extends Model
{
    use SoftDelete;

    // 表名
    protected $name = 'shopro_box_detail';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';
    protected $deleteTime = 'delete_time';

    /**
     * 抽取一个
     * @param int $box_id 盲盒
     * @param array $except 排除的商品ID
     * @return mixed
     * @throws \Exception
     * @author fuyelk <fuyelk@fuyelk.com>
     */
    public static function getOne(int $box_id, array $except = [])
    {
        // 查询该盲盒中的全部商品ID
        $goodsIds = self::where('box_id', $box_id)->column('goods_id');

        // 找出这些商品中下架、缺货或已删除的商品
        $removeGoodsIds = Goods::withTrashed()->whereIn('id', $goodsIds)
            ->where(function ($query) use ($except) {
                $query->where('status', '=', 'down')->whereOr('deletetime', 'not null')->whereOr('id', 'in', implode(',', $except));
            })->column('id');

        // 移除无效的商品，得到有效的商品ID
        $usefulGoodsIds = array_diff($goodsIds, $removeGoodsIds);

        if (empty($usefulGoodsIds)) {
            throw new \Exception('奖品不足');
        }

        // 查询有效商品的概率信息
        $prize = self::where('box_id', $box_id)->whereIn('goods_id', $usefulGoodsIds)->column('rate,goods_id', 'id');

        // 概率集合
        $prizeRate = array_column($prize, 'rate');

        // 商品ID集合
        $goodsList = array_column($prize, 'goods_id');

        return self::rand($prizeRate, $goodsList);
    }

    /**
     * 抽取多个
     * @param int $box_id 盲盒
     * @param int $box_id
     * @param int $num
     * @return array
     * @throws \Exception
     * @author fuyelk <fuyelk@fuyelk.com>
     */
    public static function getMore(int $box_id, int $num)
    {
        $goodsNum = [];
        $goodsIds = [];
        $except = [];
        while (--$num >= 0) {
            // 抽取商品
            $goods_id = self::getOne($box_id);

            // 第一次抽到，初始计数
            if (!isset($goodsNum[$goods_id])) {
                $goodsNum[$goods_id] = 0;
            }

            // 计一次数
            ++$goodsNum[$goods_id];

            $need = $goodsNum[$goods_id];
            $getNew = false; // 标记是否重新抽

            // 检查库存
            while (!self::checkStock($goods_id, $need)) {

                // 库存不够，则排除该商品，重新抽取
                $except[] = $goods_id;
                $goods_id = self::getOne($box_id, $except);

                $getNew = true;

                // 计算需要的数量
                $need = isset($goodsNum[$goods_id]) ? $goodsNum[$goods_id] + 1 : 1;
            }

            // 重新抽了,则计数
            if ($getNew) {
                if (!isset($goodsNum[$goods_id])) {
                    $goodsNum[$goods_id] = 0;
                }
                $goodsNum[$goods_id]++;
            }
            $goodsIds[] = $goods_id;
        }
        return $goodsIds;
    }

    /**
     * 随机
     * @param array $rate 中奖概率集合:
     * <pre>
     * $rate = [
     *     0 => 10, // 第二个奖品概率10%
     *     1 => 5.88, // 第二个奖品概率5.88%
     *     1 => 35.60, // 第二个奖品概率35.6%
     * ];
     * </pre>
     * @param array $goods 奖品集合，顺序与rate字段一致:
     * $rate = [
     *     0 => '第一个奖品',
     *     1 => '第二个奖品',
     *     2 => '第三奖品',
     * ];
     * @return mixed
     * @author fuyelk <fuyelk@fuyelk.com>
     * @date 2021/07/10 21:08
     */
    private static function rand($rate = [], $goods = [])
    {
        // 将数据按概率降序排序
        array_multisort($rate, SORT_DESC, $goods);
        foreach ($rate as &$item) {
            $item = round($item, 2) * 100; // 扩大100倍避免小数
        }

        //奖项的设置和概率可以手动设置化;
        $total = array_sum($rate);

        foreach ($rate as $key => $value) {
            $randNumber = mt_rand(1, $total);
            if ($randNumber <= $value) {
                $notice = $goods[$key];
                break;
            } else {
                $total -= $value;
            }
        }

        return $notice;
    }

    /**
     * 检查库存是否低于需要的数量
     * @param int $goods_id 商品ID
     * @param int $need 需要的数量
     * @return bool
     */
    private static function checkStock($goods_id, $need)
    {
        // 需要1个，则不检查库存（因为奖品池要求至少有1个）
        if (1 == $need) return true;

        $stock = Goods::where('id', $goods_id)->value('stock');
        if (empty($stock) || $stock < $need) {
            return false;
        }
        return true;
    }

}