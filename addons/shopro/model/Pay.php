<?php

namespace addons\shopro\controller;

use addons\epay\library\Service;
use addons\shopro\model\BoxOrder;
use addons\shopro\model\OrderItem;
use fast\Random;
use think\addons\Controller;
use addons\shopro\exception\Exception;
use addons\shopro\model\Order;
use addons\shopro\model\User;
use addons\shopro\model\TradeOrder;
use think\Db;
use think\Log;

class Pay extends Base
{

    protected $noNeedLogin = ['prepay', 'notifyx', 'notifyr', 'alipay'];
    protected $noNeedRight = ['*'];


    /**
     * 支付宝网页支付
     */
    public function alipay()
    {
        $order_sn = $this->request->get('order_sn');

        $order_type =input('order_type');
        if(isset($order_type) && $order_type=='boxOrder'){
            $box_order = new BoxOrder();
            $order = $box_order->field('id,out_trade_no as order_sn, rmb_amount as total_fee ,status')->where('out_trade_no', $order_sn)->find();

            if (!$order) {
                throw new \Exception("订单不存在");
            }
            if ('unpay' != $order->status) {
                throw new \Exception("该订单已支付，请勿重复支付");
            }
            $notify_url = $this->request->root(true) . '/addons/shopro/pay/notifyx/payment/alipay/platform/H5/order_type/box_order';

        }else{
            list($order, $prepay_type) = $this->getOrderInstance($order_sn);
            $order = $order->where('order_sn', $order_sn)->find();

            if (!$order) {
                throw new \Exception("订单不存在");
            }
            if ($order->status > 0) {
                throw new \Exception("订单已支付");
            }
            if ($order->status < 0) {
                throw new \Exception("订单已失效");
            }

            $notify_url = $this->request->root(true) . '/addons/shopro/pay/notifyx/payment/alipay/platform/H5';
        }

        try {

            $pay = new \addons\shopro\library\PayService('alipay', 'url', $notify_url);
    
            $order_data = [
                'order_id' => $order->id,
                'out_trade_no' => $order->order_sn,
                'total_fee' => $order->total_fee,
                'subject' => '商城订单支付',
            ];
    
            $result = $pay->create($order_data);

            $result = $result->getContent();
            
	        echo $result;
        } catch (\Exception $e) {
            echo $e->getMessage();
        }

        // $this->assign('result', $result);

        // return $this->view->fetch();
    }


    /**
     * 拉起支付
     */
    public function prepay()
    {
        checkEnv('yansongda');

        $order_sn = $this->request->post('order_sn');
        $payment = $this->request->post('payment');
        $openid = $this->request->post('openid', '');
        $platform = request()->header('platform');
        if (!$platform) $this->error("请确认平台信息");

        list($order, $prepay_type) = $this->getOrderInstance($order_sn);
        $order = $order->nopay()->where('order_sn', $order_sn)->find();

        if (!$order) {
            $this->error("订单不存在");
        }

        if (!$payment || !in_array($payment, ['wechat', 'alipay', 'wallet'])) {
            $this->error("支付类型不能为空");
        }
        $configModel = new \addons\shopro\model\Config;
        $config = $configModel->where('name', '=', 'shopro')->value('value');
        // 商城基本设置
        $shoproConfig = json_decode($config, true);
        if ((int)$shoproConfig['goods_limit']>0){
            //限购
            $count = \addons\shopro\model\Order::where(['status'=>['>',0],'goods_id'=>$order['goods_id'],'user_id'=>$this->auth->id])->count();
            if ($count>=$shoproConfig['goods_limit'])$this->error('相同藏品每人只能限购'.$shoproConfig['goods_limit'].'件');
        }
        if ($payment == 'wallet' && $prepay_type == 'order') {
            // 余额支付
            $this->walletPay($order, $payment, $platform);
        }

        $order_data = [
            'order_id' => $order->id,
            'out_trade_no' => $order->order_sn,
            'total_fee' => $order->total_fee,
        ];

        // 微信公众号，小程序支付，必须有 openid
        if ($payment == 'wechat') {
            if (in_array($platform, ['wxOfficialAccount', 'wxMiniProgram'])) {
                if (isset($openid) && $openid) {
                    // 如果传的有 openid
                    $order_data['openid'] = $openid;
                } else {
                    // 没有 openid 默认拿下单人的 openid
                    $oauth = \addons\shopro\model\UserOauth::where([
                        'user_id' => $order->user_id,
                        'provider' => 'Wechat',
                        'platform' => $platform
                    ])->find();
        
                    $order_data['openid'] = $oauth ? $oauth->openid : '';
                }
    
                if (empty($order_data['openid'])) {
                    // 缺少 openid
                    return $this->success('缺少 openid', 'no_openid');
                }
            }

            $order_data['body'] = '商城订单支付';
        } else {
            $order_data['subject'] = '商城订单支付';
        }

        $notify_url = $this->request->root(true) . '/addons/shopro/pay/notifyx/payment/' . $payment . '/platform/' . $platform ;
        $pay = new \addons\shopro\library\PayService($payment, $platform, $notify_url);

        try {
            $result = $pay->create($order_data);
        } catch (\Yansongda\Pay\Exceptions\Exception $e) {
            $this->error("支付配置错误：" . $e->getMessage());
        }
        
        if ($platform == 'App') {
            $result = $result->getContent();
        }
        if ($platform == 'H5' && $payment == 'wechat') {
            $result = $result->getContent();
        }

        return $this->success('获取预付款成功', [
            'pay_data' => $result,
            'pay_action' => $pay->method,
        ]);
    }



    // 余额支付
    public function walletPay ($order, $type, $method) {
        $order = Db::transaction(function () use ($order, $type, $method) {
            // 重新加锁读，防止连点问题
            $order = Order::nopay()->where('order_sn', $order->order_sn)->lock(true)->find();
            if (!$order) {
                $this->error("订单已支付");
            }
            $total_fee = $order->total_fee;

            // 扣除余额
            $user = User::info();

            if (is_null($user)) {
                // 没有登录，请登录
                $this->error(__('Please login first'), null, 401);
            }

            User::money(-$total_fee, $user->id, 'wallet_pay', $order->id, '',[
                'order_id' => $order->id,
                'order_sn' => $order->order_sn,
            ]);

            // 支付后流程
            $notify = [
                'order_sn' => $order['order_sn'],
                'transaction_id' => '',
                'notify_time' => date('Y-m-d H:i:s'),
                'buyer_email' => $user->id,
                'pay_fee' => $order->total_fee,
                'pay_type' => 'wallet'             // 支付方式
            ];
            $notify['payment_json'] = json_encode($notify);
            //销毁
            $orderItem = OrderItem::get(['order_id'=>$order->id]);
            $data = [
                'user_id' => $order['user_id'],
                'original_price' => $order->total_fee,
                'goods_id' => $orderItem['goods_id'],
                'type' => 1,
                'status' => 0,
            ];
            if ($orderItem['user_collect_id']) {
                //购买的寄售大厅的  原资产会进行资产转移
                $collect = \addons\shopro\model\UserCollect::where('id', $orderItem['user_collect_id'])->find();
                $collect->is_consume = 1;//资产销毁
                $collect->status = 2;
                $collect->status_time = time();
                $collect->save();
                //链上资产转移
                $res = \addons\shopro\model\UserCollect::transferShard($collect['asset_id'], $collect['user_id'], $order['user_id'], $collect['nft_id']);
                Log::info('购买寄售大厅链上资产转移:::' . json_encode($res));
                $data['notShard'] = 1;
                $data['asset_id'] = $collect['asset_id'];
                $data['sn'] = $collect['sn'];
                $data['operation_id'] = $res['data']['operation_id']??'';
                $data['task_id'] = $res['data']['task_id']??"";
            }
            if ($orderItem['goods_id']) {
                //购买 todo:上链
                $res = \addons\shopro\model\UserCollect::edit($data);
            }

            $order->paymentProcess($order, $notify);

            return $order;
        });

        $this->success('支付成功', $order);
    }


    /**
     * 支付成功回调
     */
    public function notifyx()
    {
        Log::write('notifyx-comein:');

        $payment = $this->request->param('payment');
        $platform = $this->request->param('platform');
        $order_type = $this->request->param('order_type');
        $order_type = isset($order_type)?$this->request->param('order_type'):'';

        $pay = new \addons\shopro\library\PayService($payment, $platform);

        $result = $pay->notify(function ($data, $pay) use ($payment, $platform,$order_type) {
            Log::write('notifyx-result:'. json_encode($data));
            // {    // 微信回调参数
            //     "appid":"wx39cd0799d4567dd0",
            //     "bank_type":"OTHERS",
            //     "cash_fee":"1",
            //     "fee_type":"CNY",
            //     "is_subscribe":"N",
            //     "mch_id":"1481069012",
            //     "nonce_str":"dPpcZ6AzCDU8daNC",
            //     "openid":"oD9ko4x7QMDQPZEuN8V5jtZjie3g",
            //     "out_trade_no":"202010240834057843002700",
            //     "result_code":"SUCCESS",
            //     "return_code":"SUCCESS",
            //     "sign":"3103B6D06F13D2B3959C5B3F7F1FD61C",
            //     "time_end":"20200407102424",
            //     "total_fee":"1",
            //     "trade_type":"JSAPI",
            //     "transaction_id":"4200000479202004070804485027"
            // }

            // {    // 支付宝支付成功回调参数
            //     "gmt_create":"2020-04-10 19:15:00",
            //     "charset":"utf-8",
            //     "seller_email":"xptech@qq.com",
            //     "subject":"\u5546\u57ce\u8ba2\u5355\u652f\u4ed8",
            //     "sign":"AOiYZ1a2mEMOuIbHFCi6V6A0LJ97UMiHsCWgNdSU9dlzKFl15Ts8b0mL\/tN+Hhskl+94S3OUiNTBD3dD0Kv923SqaTWxNdj533PCdo2GDKsZIZgKbavnOvaccSKUdmQRE9KtmePPq9V9lFzEQvdUkKq1M8KAWO5K9LTy2iT2y5CUynpiu\/04GVzsTL9PqY+LDwqj6K+w7MgceWm1BWaFWg27AXIRw7wvsFckr3k9GGajgE2fufhoCYGYtGFbhGOp6ExtqS5RXBuPODOyRhBLpD8mwpOX38Oy0X+R4YQIrOi02dhqwPpvw79YjnvgXY3qZEQ66EdUsrT7EBdcPHK0Gw==",
            //     "buyer_id":"2088902485164146",
            //     "invoice_amount":"0.01",
            //     "notify_id":"2020041000222191501064141414240102",
            //     "fund_bill_list":"[{\"amount\":\"0.01\",\"fundChannel\":\"PCREDIT\"}]",
            //     "notify_type":"trade_status_sync",
            //     "trade_status":"TRADE_SUCCESS",
            //     "receipt_amount":"0.01",
            //     "buyer_pay_amount":"0.01",
            //     "app_id":"2021001114666742",
            //     "sign_type":"RSA2",
            //     "seller_id":"2088721922277739",
            //     "gmt_payment":"2020-04-10 19:15:00",
            //     "notify_time":"2020-04-10 19:15:01",
            //     "version":"1.0",
            //     "out_trade_no":"202007144778322770017000",
            //     "total_amount":"0.01",
            //     "trade_no":"2020041022001464141443020240",
            //     "auth_app_id":"2021001114666742",
            //     "buyer_logon_id":"157***@163.com",
            //     "point_amount":"0.00"
            // }

            // {   // 支付宝退款成功（交易关闭）回调参数
            //     "gmt_create": "2020-08-15 14:48:32",
            //     "charset": "utf-8",
            //     "seller_email": "xptech@qq.com",
            //     "gmt_payment": "2020-08-15 14:48:32",
            //     "notify_time": "2020-08-15 16:11:45",
            //     "subject": "商城订单支付",
            //     "gmt_refund": "2020-08-15 16:11:45.140",
            //     "sign": "b6ArkgzLIRteRL9FMGC6i\/jf6VwFYQbaGDGRx002W+pdmN5q9+O4edZ3ALF74fYaijSl9ksNr0dKdvanu3uYxBTcd\/GIS4N1CWzmCOv6pzgx5rO\/YvGoHLM3Yop0GKKuMxmnNsZ6jhYKEY7SYD3Y0L6PU9ZMdHV7yIiVj+zZmbKzUgK9MPDCEXs+nzpNAiSM8GTqYRSUvKobAK68hswG2k1QIcqr5z+ZmVYa\/nHHkoC9qXt5zwyGi4P+2eOsr6V2PjA3x8qqe7TN5aI1DeoZD5KqHPYYaYF17J2q6YPlgl3WUl1RhE7H86bivB1fIuYEv\/3+JR74WN\/o7krGw1RPHg==",
            //     "out_biz_no": "R202004114414846255015300",
            //     "buyer_id": "2088902485164146",
            //     "version": "1.0",
            //     "notify_id": "2020081500222161145064141453349793",
            //     "notify_type": "trade_status_sync",
            //     "out_trade_no": "202002460317545607015300",
            //     "total_amount": "0.01",
            //     "trade_status": "TRADE_CLOSED",
            //     "refund_fee": "0.01",
            //     "trade_no": "2020081522001464141438570535",
            //     "auth_app_id": "2021001114666742",
            //     "buyer_logon_id": "157***@163.com",
            //     "gmt_close": "2020-08-15 16:11:45",
            //     "app_id": "2021001114666742",
            //     "sign_type": "RSA2",
            //     "seller_id": "2088721922277739"
            // }

            try {
                $out_trade_no = $data['out_trade_no'];
                $out_refund_no = $data['out_biz_no'] ?? '';

                if($order_type){

                    // 判断支付宝微信是否是支付成功状态，如果不是，直接返回响应
                    if ($payment == 'alipay' && $data['trade_status'] != 'TRADE_SUCCESS') {
                        // 不是交易成功的通知，直接返回成功
                        return $pay->success()->send();
                    }
                    if ($payment == 'wechat' && ($data['result_code'] != 'SUCCESS' || $data['return_code'] != 'SUCCESS')) {
                        // 微信交易未成功，返回 false，让微信再次通知
                        return false;
                    }

                    // 支付成功流程
                    $pay_fee = $payment == 'alipay' ? $data['total_amount'] : $data['total_fee'] / 100;

                    $box_order = new BoxOrder();

                    $order = $box_order->where('out_trade_no', $out_trade_no)->find();

                    $box_model = new \addons\shopro\model\Box();

                    if (!$order || $order->status !='unpay') {
                        // 订单不存在，或者订单已支付
                        return $pay->success()->send();
                    }
                    Db::startTrans();
                    //执行订单更新操作
                    $notify = ['status' => 'unused'];
                    $orderresult =  $order->save($notify);
                    if($orderresult){
                        Db::commit();
                    }else{
                        Db::rollback();
                        return $pay->success()->send();
                    }
                    $order = $box_order->where('out_trade_no', $out_trade_no)->find();
                    if($order->status!="unused"){
                        return $pay->success()->send();
                    }
                    //执行订单更新操作
                    $notify = [
                        'out_trade_no' => $data['out_trade_no'],
                        'transaction_id' => $payment == 'wechat' ? $data['transaction_id'] :'',
                        'alipay_trade_no' => $payment == 'alipay' ? $data['trade_no'] : '',
                        'pay_time' => strtotime($data['gmt_payment']),
                        'pay_rmb' => $pay_fee,
                        'pay_coin' => $pay_fee,
                        'backend_read' => 0,
                        'pay_method' => $payment              // 支付方式
                    ];

                    $order->save($notify);

                    // 扣除库存;
                    $box_model->setDec('sales_num',$order->num);

                    $user = User::get(['id'=>$order->user_id]);
                    //查询user的父级ID,新增一个推荐
                    if($user->parent_user_id>0){
                        $shareModel = new \addons\shopro\model\Share();
                        $share = $shareModel->where(['user_id'=>$user->id,'share_id'=>$user->parent_user_id])->find();
                        if(!$share){
                            $shareData = [
                                'user_id'=>$user->id,
                                'share_id'=>$user->parent_user_id
                            ];
                            $shareModel->insert($shareData);

                            //查询父级用户,并且增加推广人数
                            // 查询一下 分享人ID 是否存在,不存在就+1
                            $p_user = \addons\shopro\model\User::where(['id'=>$user->parent_user_id])->find();
                            $p_user->curr_share_count +=1;
                            $p_user->share_count +=1;
                            if($p_user->share_count%4==0 && $p_user->share_count<41){
                                $p_user->box_num +=1;
                            }
                            $p_user->save();
                        }
                    }
                    return $pay->success()->send();
                }else{


                    list($order, $prepay_type) = $this->getOrderInstance($out_trade_no);

                    // 判断是否是支付宝退款（支付宝退款成功会通知该接口）
                    if ($payment == 'alipay'    // 支付宝支付
                        && $data['notify_type'] == 'trade_status_sync'      // 同步交易状态
                        && $data['trade_status'] == 'TRADE_CLOSED'          // 交易关闭
                        && $out_refund_no                                   // 退款单号
                    ) {
                        // 退款回调
                        if ($prepay_type == 'order') {
                            // 退款回调
                            $this->refundFinish($out_trade_no, $out_refund_no);
                        } else {
                            // 其他订单如果支持退款，逻辑这里补充
                        }

                        return $pay->success()->send();
                    }


                    // 判断支付宝微信是否是支付成功状态，如果不是，直接返回响应
                    if ($payment == 'alipay' && $data['trade_status'] != 'TRADE_SUCCESS') {
                        // 不是交易成功的通知，直接返回成功
                        return $pay->success()->send();
                    }
                    if ($payment == 'wechat' && ($data['result_code'] != 'SUCCESS' || $data['return_code'] != 'SUCCESS')) {
                        // 微信交易未成功，返回 false，让微信再次通知
                        return false;
                    }

                    // 支付成功流程
                    $pay_fee = $payment == 'alipay' ? $data['total_amount'] : $data['total_fee'] / 100;

                    //你可以在此编写订单逻辑
                    $order = $order->where('order_sn', $out_trade_no)->find();

                    if (!$order || $order->status > 0) {
                        // 订单不存在，或者订单已支付
                        return $pay->success()->send();
                    }

                    Db::transaction(function () use ($order, $data, $payment, $platform, $pay_fee, $prepay_type) {
                        $notify = [
                            'order_sn' => $data['out_trade_no'],
                            'transaction_id' => $payment == 'alipay' ? $data['trade_no'] : $data['transaction_id'],
                            'notify_time' => date('Y-m-d H:i:s', strtotime($data['time_end'])),
                            'buyer_email' => $payment == 'alipay' ? $data['buyer_logon_id'] : $data['openid'],
                            'payment_json' => json_encode($data->all()),
                            'pay_fee' => $pay_fee,
                            'pay_type' => $payment              // 支付方式
                        ];
                        //销毁
                        $orderItem = OrderItem::get(['order_id' => $order['id']]);
                        $data = [
                            'user_id' => $order['user_id'],
                            'goods_id' => $orderItem['goods_id'],
                            'original_price' => $payment == 'alipay' ? $data['total_amount'] : $data['total_fee'],
                            'type' => 1,
                            'status' => 0,
                        ];
                        if ($orderItem['user_collect_id']) {
                            $collect = \addons\shopro\model\UserCollect::where('id', $orderItem['user_collect_id'])->find();
                            $collect->is_consume = 1;//链上 资产是否销毁
                            $collect->status = 2;
                            $collect->status_time = time();
                            $collect->save();
                            //链上资产转移
                            $res = \addons\shopro\model\UserCollect::transferShard($collect['asset_id'], $collect['user_id'], $order['user_id'], $collect['nft_id']);
                            Log::info('购买寄售大厅链上资产转移:::' . json_encode($res));
                            $data['notShard'] = 1;
                            $data['asset_id'] = $collect['asset_id'];
                            $data['sn'] = $collect['sn'];
                            $data['operation_id'] = $res['data']['operation_id']??'';
                            $data['task_id'] = $res['data']['task_id']??'';
                        }
                        if ($orderItem['goods_id']) {
                            //购买 todo:上链
                            $res = \addons\shopro\model\UserCollect::edit($data);
                        }
                        $order->paymentProcess($order, $notify);
                    });

                    return $pay->success()->send();
                }


            } catch (\Exception $e) {
                Log::write('notifyx-error:' . json_encode($e->getMessage()));
            }
        });

        return $result;
    }


    /**
     * 退款成功回调
     */
    public function notifyr()
    {
        Log::write('notifyreturn-comein:');

        $payment = $this->request->param('payment');
        $platform = $this->request->param('platform');

        $pay = new \addons\shopro\library\PayService($payment, $platform);

        $result = $pay->notifyRefund(function ($data, $pay) use ($payment, $platform) {
            Log::write('notifyr-result:' . json_encode($data));
            try {
                $out_refund_no = $data['out_refund_no'];
                $out_trade_no = $data['out_trade_no'];

                // 退款
                $this->refundFinish($out_trade_no, $out_refund_no);
                
                return $pay->success()->send();
            } catch (\Exception $e) {
                Log::write('notifyreturn-error:' . json_encode($e->getMessage()));
            }
        });

        return $result;
    }


    private function refundFinish($out_trade_no, $out_refund_no)
    {
        $order = Order::where('order_sn', $out_trade_no)->find();
        $refundLog = \app\admin\model\shopro\order\RefundLog::where('refund_sn', $out_refund_no)->find();

        if (!$order || !$refundLog || $refundLog->status != 0) {
            // 订单不存在，或者订单已退款
            return true;
        }

        $item = \app\admin\model\shopro\order\OrderItem::where('id', $refundLog->order_item_id)->find();

        Db::transaction(function () use ($order, $item, $refundLog) {
            \app\admin\model\shopro\order\Order::refundFinish($order, $item, $refundLog);
        });

        return true;
    }


    /**
     * 根据订单号获取订单实例
     *
     * @param [type] $order_sn
     * @return void
     */
    private function getOrderInstance($order_sn)
    {
        $prepay_type = 'order';
        if (strpos($order_sn, 'TO') === 0) {
            // 充值订单
            $prepay_type = 'recharge';
            $order = new TradeOrder();
        } else {
            // 订单
            $order = new Order();
        }

        return [$order, $prepay_type];
    }

    public function checkPwd()
    {
        $pwd = $this->request->param('pwd');
        $ret = $this->auth->checkpwd($pwd);
        if ($ret) {
            $this->success('校验成功');
        } else {
            $this->error($this->auth->getError());
        }
    }
}
