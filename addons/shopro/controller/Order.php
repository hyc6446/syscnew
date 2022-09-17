<?php

namespace addons\shopro\controller;


class Order extends Base
{

    protected $noNeedLogin = [];
    protected $noNeedRight = ['*'];


    public function index()
    {
        $params = $this->request->get();

        $type = isset($params['type']) ? $params['type'] : 0;

        if ($type === 'box') {
            $box_order = new Box();
            $this->success('订单列表', $box_order->boxOrderList($params));
        } else {
            $this->success('订单列表', \addons\shopro\model\Order::getList($params));
        }
    }



    public function detail()
    {
        $params = $this->request->get();
        $this->success('订单详情', \addons\shopro\model\Order::detail($params));
    }


    public function itemDetail()
    {
        $params = $this->request->get();
        $this->success('订单商品', \addons\shopro\model\Order::itemDetail($params));
    }


    // 即将废弃
    public function statusNum()
    {
        $this->success('订单数量', \addons\shopro\model\Order::statusNum());
    }


    // 取消订单
    public function cancel()
    {
        $params = $this->request->post();

        // 表单验证
        $this->shoproValidate($params, get_class(), 'cancel');

        $this->success('取消成功', \addons\shopro\model\Order::operCancel($params));
    }

    // 删除订单
    public function delete()
    {
        $params = $this->request->post();

        // 表单验证
        $this->shoproValidate($params, get_class(), 'delete');

        $this->success('删除成功', \addons\shopro\model\Order::operDelete($params));
    }

    // 确认收货
    public function confirm()
    {
        $params = $this->request->post();

        // 表单验证
        $this->shoproValidate($params, get_class(), 'confirm');

        $this->success('收货成功', \addons\shopro\model\Order::operConfirm($params));
    }


    public function comment()
    {
        $params = $this->request->post();

        // 表单验证
        $this->shoproValidate($params, get_class(), 'comment');

        $this->success('评价成功', \addons\shopro\model\Order::operComment($params));
    }


    public function pre()
    {
        $params = $this->request->post();

        // 表单验证
        $this->shoproValidate($params, get_class(), 'pre');

        $result = \addons\shopro\model\Order::pre($params);

        if (isset($result['msg']) && $result['msg']) {
            $this->error($result['msg'], $result);
        } else {
            $this->success('计算成功', $result);
        }
    }


    public function createOrder()
    {
        // $this->error('已售罄');
        $post = $this->request->post();
        $params['goods_list'] = [];
        $params['goods_list'][0]['goods_id'] = $post['goods_id'];
        $params['goods_list'][0]['goods_num'] = $post['amount'];
        $params['goods_list'][0]['sku_price_id'] = $post['sku_price_id'];
        $params['goods_list'][0]['goods_price'] = $post['goods_price'];
        $params['goods_list'][0]['dispatch_type'] = 'autosend';
        $params['goods_list'][0]['dispatch_id'] = 1;
        $params['goods_list'][0]['activity_type'] = $post['activity'];
        $params['address_id'] = '';
        $params['buy_type'] = 'alone';
        $params['coupons_id'] = 0;
        $params['from'] = 'goods';

        $configModel = new \addons\shopro\model\Config;
        $config = $configModel->where('name', '=', 'shopro')->value('value');
        // 商城基本设置
        $shoproConfig = json_decode($config, true);
        if (!$post['activity'] == "priority" && (int)$shoproConfig['goods_limit'] > 0) {
            //限购
            $order_ids = \addons\shopro\model\OrderItem::where(['goods_id' => $post['goods_id'], 'user_id' => $this->auth->id])->column('order_id');
            $count = \addons\shopro\model\Order::where(['id' => ['in', $order_ids], 'status' => ['>', 0]])->count();
            if ($count >= $shoproConfig['goods_limit']) $this->error('相同藏品每人只能限购' . $shoproConfig['goods_limit'] . '件');
        }

        // 表单验证
        $this->shoproValidate($params, get_class(), 'createOrder');

        //验证藏品是否可购买
        $goods = \addons\shopro\model\Goods::getGoodsDetail($post['goods_id']);

        // 判断库存
        if($goods['issue_num'] == $goods['sales']){
            $this->error('您没有该藏品当前库存不足');
        }

        // 处理优先购逻辑
        if ($post['activity'] == "priority") {
            if ($goods['sales_time'] && $goods['sales_time'] > time()) {
                $priority =  \addons\shopro\model\PriorityBuy::buyerList($this->auth->id);
                if ($priority->status != 1) {
                    $this->error('您没有该藏品的购买资格');
                }
                $num = $params['goods_list'][0]['goods_num'];
                if ($num > $priority->buy_num) {
                    $this->error('无效的购买次数');
                }
            } else {
                \addons\shopro\model\PriorityBuy::deleteState($this->auth->id);
                $this->error('当前活动已截止');
            }
        } else {
            if ($goods['sales_time'] && $goods['sales_time'] > time()) {
                $this->error('该藏品的售卖时间还未开始请稍等');
            } else {
                // $this->error('该藏品的售卖时间已截止');
            }
        }
        $order = \addons\shopro\model\Order::createOrder($params);

        $this->success('订单添加成功', $order);
    }
    // 获取可用优惠券列表
    public function coupons()
    {
        $params = $this->request->post();

        // 表单验证
        $this->shoproValidate($params, get_class(), 'coupons');

        $coupons = \addons\shopro\model\Order::coupons($params);

        $this->success('获取成功', $coupons);
    }
}
