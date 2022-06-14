<?php


namespace addons\shopro\controller;


use addons\shopro\model\BoxOrder;
use addons\shopro\model\Detail;
use addons\shopro\model\Box as BoxModel;
use addons\shopro\model\Goods;
use addons\shopro\model\GoodsSkuPrice;
use addons\shopro\model\PrizeRecord;
use think\Db;
use think\Exception;

class Box extends Base
{
    protected $noNeedLogin = ['recommend','boxDetail','getPrizeRecord'];
    protected $noNeedRight = ['*'];

    public function recommend()
    {
        $pagesize = input('pagesize/d', 10);
        $page = input('page/d', 1);
        $category_id = input('category_id/d', '');

        // 查询空盲盒
        $existboxid = Detail::field('box_id')->distinct(true)->buildSql();
        $emptyBoxIds = BoxModel::where('id', 'exp', 'not in ' . $existboxid)->column('id');
        $where = ['a.switch'=>1];
        if($category_id){
            $where['a.category_id'] = $category_id;
        }


        $list = BoxModel::alias('box')->alias('a')
            ->field('a.id box_id,a.box_name,a.coin_price,b.name cate_name,b.weigh,a.category_id,b.color')
            ->join('shopro_box_category b','a.category_id=b.id')
            ->whereNotIn('a.id', $emptyBoxIds)
            ->order('sort', 'asc')
            ->where($where)
            ->paginate($pagesize, false, ['page' => $page])
            ->each(function ($item) {
                // 查询前6个商品图片
                // $firstGoods = Detail::where('box_id', $item->box_id)->order('weigh', 'desc')->limit(6)->column('goods_id');
                // $goods_images = Goods::whereIn('id', $firstGoods)->column('image');
                $goods_images = Detail::alias('a')->join("shopro_goods b","b.id = a.goods_id")->where('a.box_id', $item->box_id)->order('a.weigh', 'desc')->limit(6)->column('b.image');

                foreach ($goods_images as &$image) {
                    $image = cdnurl($image, true);
                }

                // 查询最低价
                $allGoods = Detail::where('box_id', $item->box_id)->column('goods_id');
                $item->goods_num = count($allGoods);

                $min_coin_price = Goods::whereIn('id', $allGoods)->order('price', 'asc')->value('price');
                $item->price_min = round($min_coin_price, 2);

                $max_coin_price = Goods::whereIn('id', $allGoods)->order('price', 'desc')->value('price');
                $item->price_max = round($max_coin_price, 2);

                $item->goods_images = $goods_images;
            });

         $this->success('查询成功', $list);
        
    }

    public function myBox()
    {
        $status = input('status/d');
        $pagesize = input('pagesize/d', 10);
        $page = input('page/d', 1);
        $statusList = [1 => 'bag', 2 => 'exchange'];

        if (!isset($statusList[$status])) {
            $this->error('状态有误');
        }
        $status = $statusList[$status];

        $order = 'prize.id desc';
        if ('exchange' == $status) {
            $order = 'exchange_time desc';
        }

        $list = PrizeRecord::alias('prize')
            ->field('prize.id record_id,prize.goods_name,prize.goods_image,prize.create_time,prize.exchange_time')
            ->field('order.coin_price box_coin_price,order.rmb_price box_rmb_price,order.pay_method')
            ->join('shopro_box_order order', 'order.id = prize.order_id')
            ->where('prize.user_id', $this->auth->id)
            ->where('prize.status', $status) // 奖品状态:bag=盒柜,exchange=已回收,delivery=申请发货,received=已收货
            ->order($order)
            ->paginate($pagesize, false, ['page' => $page])
            ->each(function ($item) use ($status) {
                $item->goods_image = $item->goods_image ? cdnurl($item->goods_image, true) : '';

                $item->box_coin_price = intval($item->box_coin_price);
                $item->box_rmb_price = floatval($item->box_rmb_price);

                if ('exchange' == $status) {
                    $item->time = date('Y-m-d H:i:s', $item->exchange_time);
                } else {
                    $item->time = date('Y-m-d H:i:s', $item->create_time);
                }
                $item->hidden(['create_time', 'exchange_time']);
            });

        $this->success('查询成功', $list);
        
    }


    public function boxDetail()
    {
        $box_id = input('box_id/d');
        if (empty($box_id)) {
            $this->error('请选择盲盒');
        }

        // 查询金币余额
//        $mycoin = $this->auth->isLogin() ? $this->auth->money : 0;

        // 查询是否收藏
//        $is_star = $this->auth->isLogin() ? Star::check($this->auth->id, $box_id) : 0;

        // 查询盲盒基础信息
        $box = BoxModel::field('box_banner_images,box_banner_images_desc,box_name,coin_price')->where('id', $box_id)->find();
        if (empty($box)) {
            $this->error('盲盒有误');
        }

        $firstGoods =  Detail::alias("a")
            ->join("shopro_goods b","b.id = a.goods_id")
            ->join('shopro_category c','b.category_ids=c.id')
            ->where('a.box_id', $box_id)->field("b.image,b.price,b.title,c.name,c.color,a.rate")->order("a.weigh desc")->select();

        foreach ($firstGoods as &$first) {
            $first->image = $first->image ? cdnurl($first->image, true) : $first->image;
            $first->price = round($first->price, 2);
            $first->hidden(['coin_price']);
        }


        $box_banner_images = [];
        $box_banner = [];
        $box->box_banner_images = explode(',', $box->box_banner_images);
        $banner_desc = $box->box_banner_images_desc;
        $banner_desc = $banner_desc ? json_decode($banner_desc, true) : [];
        foreach ($box->box_banner_images as $index => $image) {
            $image = $image ? cdnurl($image, true) : '';
            $box_banner_images[] = $image;
            $box_banner[] = [
                'desc' => $banner_desc[$index] ?? '',
                'image' => $image
            ];
        }

        $ret = [
            'box' => $box,
            'box_banner_images' => $box_banner_images,
            'box_banner' => $box_banner,
            'coin_price' => intval($box->coin_price),
            'goodslist' => $firstGoods,
        ];

        $this->success('查询成功', $ret);
    }


    /**
     * 获取盲盒的开奖记录
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getPrizeRecord()
    {
        $box_id = input('box_id/d','');
        $where = [];
        if($box_id){
            $where['prize.box_id'] = $box_id;
        }
        // 查询该盲盒开箱记录
        $prize = Prizerecord::alias('prize')
            ->field('prize.goods_name,prize.goods_image,prize.goods_rmb_price,prize.create_time')
            ->field('user.nickname,user.avatar')
            ->field('gc.name cate_name,gc.color')
            ->join('user user', 'user.id = prize.user_id')
            ->join('shopro_goods g','g.id=prize.goods_id')
            ->join('shopro_category gc','gc.id=g.category_ids')
            ->where($where)
            ->order('prize.id', 'desc')
            ->limit(10)
            ->select();

        foreach ($prize as $prize_item) {
            $prize_item->create_time = date('Y-m-d H:i:s', $prize_item->create_time);
            $prize_item->avatar = $prize_item->avatar ? $prize_item->avatar : letter_avatar($prize_item->nickname);
            $prize_item->goods_image = $prize_item->goods_image ? $prize_item->goods_image : '';
        }

        $this->success('查询成功',$prize);

    }

    /**
     * 创建盲盒订单
     * @author fuyelk <fuyelk@fuyelk.com>
     */
    public function createOrder()
    {
        $box_id = input('box_id/d');
        $num = input('num/d');
        $select = input('select', '');

        if (empty($box_id)) {
            $this->error('未选择盲盒');
        }

        if (!in_array($num, [1, 5, 9])) {
            $this->error('选择的盲盒数量有误');
        }

        // 检查盲盒
        $box = BoxModel::field('id,box_name,box_banner_images,coin_price')->where('id', $box_id)->find();
        if (empty($box)) {
            $this->error('选择的盲盒有误');
        }

        // 检查盲盒奖品
        $prize = Detail::where('box_id', $box_id)->order('weigh desc,id asc')->value('goods_id');
        if (empty($prize)) {
            $this->error('这个盲盒太火爆吧，商品暂时缺货！');
        }

        // 查询前6个商品的图片
        $goodsIds = Detail::where('box_id', $box_id)->order('weigh desc,id asc')->column('goods_id');
        $goodsImages = Goods::whereIn('id', $goodsIds)
            ->column('image');
        foreach ($goodsImages as &$image) {
            $image = cdnurl($image, true);
        }

        Db::startTrans();
        try {

            $rmb_price = round($box->coin_price, 2);
            $res = BoxOrder::create([
                'box_id' => $box_id,
                'box_name' => $box->box_name,
                'image' => $goodsImages[0] ?? '',
                'coin_price' => $box->coin_price,
                'rmb_price' => $rmb_price,
                'num' => $num,
                'coin_amount' => $box->coin_price * $num,
                'rmb_amount' => $rmb_price * $num,
                'user_id' => $this->auth->id,
                'select' => $select,
                'out_trade_no' => date('YmdHis') . mt_rand(10000, 99999)
            ]);

            $ret = [
                'order_id' => intval($res->id),
                'box_name' => $res->box_name,
                'images' => $goodsImages,
                'coin_amount' => $res->coin_amount,
                'rmb_amount' => $res->rmb_amount,
                'coin_not_enough' => !!(intval($this->auth->money) < intval($res->coin_amount)),
                'alipay' => $this->request->domain() . '/api/alipay/boxpay/orderid/' . intval($res->id),
                'wechat' => '/api/wechat/boxpay/orderid/' . intval($res->id),
            ];
        } catch (Exception $e) {
            Db::rollback();
            $this->error('创建订单失败');
        }
        Db::commit();

        $this->success('创建订单成功', $ret);
    }


    /**
     * 支付订单
     */
    public function payOrder()
    {
        //
        $order_id = input('order_id/d');
        $pay_method = input('pay_method/d','wallet');// wallet=金币,wechat=微信,alipay=支付宝'

        if($pay_method == 'wallet'){
            $this->coinPay();
        }


        if($pay_method == 'wechat'){

        }

        if($pay_method == 'alipay'){

        }
    }



    public function coinPay()
    {
        $order_id = input('order_id/d');
        if (empty($order_id)) {
            $this->error('请选择支付订单');
        }

        $order = BoxOrder::field('id,box_id,num,status,coin_amount,out_trade_no,select')->lock(true)
            ->where('id', $order_id)
            ->where('user_id', $this->auth->id)
            ->find();

        if (empty($order)) {
            $this->error('订单不存在');
        }

        if ('unpay' != $order->status) {
            $this->error('该订单已支付，请勿重复支付');
        }

        // 查询用户余额
        if (intval($this->auth->money) < $order->coin_amount) {
            $this->error('您的金币不足');
        }

        Db::startTrans();

        try {
            // 更新订单信息
            $order->pay_method = 'coin';
            $order->pay_coin = $order->coin_amount;
            $order->pay_time = time();
            $order->status = 'unused';// 状态:unpay=待支付,unused=待抽奖,used=已使用
            $order->backend_read = 0;
            $order->save();

            $coin_before = $this->auth->money;

            // 减少金币余额
            $user = $this->auth->getUser();
            $user->setDec('money', $order->pay_coin);

            // 创建余额支付记录
            \addons\shopro\model\User::money(-$order->pay_coin, $user->id, 'box_pay', $order->id, '',[
                'order_id' => $order->id,
                'box_id' => $order->box_id,
            ]);

        } catch (\Exception $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }

        Db::commit();

        // 开箱
        $prize = $this->open($order);

        $this->success('支付成功', ['prize' => $prize]);
    }


    /**
     * 盲盒开奖
     * @param Order $order
     * @return array|bool
     */
    private function open(BoxOrder $order)
    {
        // 检查订单状态
        if ('unused' != $order->status) { // 状态:unpay=待支付,unused=待抽奖,used=已使用
            return false;
        }

        if (empty($order->box_id)) {
            $this->error('请选择盲盒');
        }
        if (!in_array($order->num, [1, 5, 9])) {
            $this->error('开箱数量有误');
        }

        // 检查盲盒是否可以试玩
        $box = BoxModel::where('id', $order->box_id)->value('id');
        if (empty($box)) {
            $this->error('盲盒有误');
        }

        try {
            // 抽奖 begin
            if (1 == $order->num) {
                $goodsIds = [Detail::getOne($order->box_id)];
            } else {
                $goodsIds = Detail::getMore($order->box_id, $order->num);
            }
        } catch (\Exception $e) {

            // 退款
//            if (!$this->refund($order)) {
//                $logID = "";
//                try {
//                    $logID = dta($order->toArray(), '用户退款失败');
//                } catch (Exception $e) {
//                    $logID = dta(['order_id' => $order->id], '用户退款失败');
//                }
//                $this->error('库存不足退款失败,请截屏联系平台:' . $logID);
//            }

            $this->error('抽奖失败，已退款');
        }
        // 抽奖 end

        // 查询抽中的奖品信息
        foreach ($goodsIds as $goodsId) {
            $goodsInfo[] = Goods::alias('a')
                ->field('a.*,b.name as cate_name,b.color')
                ->join('shopro_category b','b.id=a.category_ids')
                ->where('a.id', $goodsId)->find();
        }


        // 抽奖失败
        if (empty($goodsInfo)) {
            // 退款
//            if (!$this->refund($order)) {
//                $logID = "";
//                try {
//                    $logID = dta($order->toArray(), '用户退款失败');
//                } catch (Exception $e) {
//                    $logID = dta(['order_id' => $order->id], '用户退款失败');
//                }
//                $this->error('库存不足退款失败,请截屏联系平台:' . $logID);
//            }

            $this->error('抽奖失败，已退款');
        }

        $prizeInfo = [];

        Db::startTrans();
        try {
            foreach ($goodsInfo as &$goods) {

                // 创建开箱记录
                $prize = Prizerecord::create([
                    'box_id' => $order->box_id,
                    'order_id' => $order->id,
                    'out_trade_no' => $order->out_trade_no,
                    'user_id' => $this->auth->id,
                    'goods_id' => $goods->id,
                    'goods_name' => $goods->title,
                    'goods_image' => $goods->image,
                    'goods_coin_price' => $goods->price,
                    'goods_rmb_price' => round($goods->price, 2),
                    'status' => 'bag', // 奖品状态:bag=盒柜,exchange=已回收,delivery=申请发货,received=已收货
                ]);

                //添加到我的藏品中
                \addons\shopro\model\UserCollect::create([
                    'goods_id' => $goods->id,
                    'user_id' => $this->auth->id,
                    'original_price' => $goods->original_price,
                    'price' => $goods->price,
                    'type' => 4,
                    'token' => md5($this->auth->id.'token-'.$this->auth->referral_code.time()),
                    'up_brand' => "-",
                    'auth_brand' => "-",
                    'card_id' => md5($this->auth->id.'card_id-'.$this->auth->referral_code.time()),
                    'trans_hash' => md5($this->auth->id.'trans_hash-'.$this->auth->referral_code.time()),
                    'card_time' => time(),
                    'add' => $this->auth->referral_code.time(),
                    'up_num' => 1,
                ]);
                // 减少商品库存
                GoodsSkuPrice::where('goods_id', $goods->id)->setDec('stock');

                $prizeInfo[] = [
                    'prize_id' => intval($prize->id),
                    'image' => $prize->goods_image ? cdnurl($prize->goods_image, true) : '',
                    'goods_name' => $prize->goods_name,
                    'cate_name' =>$goods->cate_name,
                    'color' =>$goods->color,
                ];
            }

            // 增加盲盒销量
            BoxModel::where('id', $order->box_id)->setInc('sales', $order->num);

        } catch (\Exception $e) {

            Db::rollback();
            // 退款
//            if (!$this->refund($order)) {
//                $logID = "";
//                try {
//                    $logID = dta($order->toArray(), '用户退款到金币失败');
//                } catch (Exception $e) {
//                    $logID = dta(['order_id' => $order->id], '用户退款失败');
//                }
//                $this->error('库存不足退款失败,请截屏联系平台:' . $logID);
//            }
            $this->error($e->getMessage());
            $this->error('抽奖失败，已退款');
        }

        Db::commit();

        // 订单状态改为已使用
        $order->save(['status' => 'used', 'backend_read' => 0]);

        $ret = [
            'select' => $order->select,
            'prizeInfo' => $prizeInfo
        ];

        return $ret;
    }





















}