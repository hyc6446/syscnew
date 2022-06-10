<?php

namespace addons\shopro\controller;


class Order extends Base
{

    protected $noNeedLogin = [];
    protected $noNeedRight = ['*'];


    public function index()
    {
        $params = $this->request->get();

        $this->success('订单列表', \addons\shopro\model\Order::getList($params));
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
        $post = $this->request->post();
        $params['goods_list'] = [];
        $params['goods_list'][0]['goods_id'] = $post['goods_id'];
        $params['goods_list'][0]['goods_num'] = 1;
        $params['goods_list'][0]['sku_price_id'] = $post['sku_price_id'];
        $params['goods_list'][0]['goods_price'] = $post['goods_price'];
        $params['goods_list'][0]['dispatch_type'] = 'autosend';
        $params['address_id'] = '';
        $params['buy_type'] = 'alone';
        $params['coupons_id'] = 0;
        $params['from'] = 'goods';
        // 表单验证
        $this->shoproValidate($params, get_class(), 'createOrder');

        //验证藏品是否可购买
        $goods = \addons\shopro\model\Goods::getGoodsDetail($post['goods_id']);
        if ($goods['sales_time'] && $goods['sales_time']> time()) {
            $this->error('预售藏品暂不支持购买');
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
