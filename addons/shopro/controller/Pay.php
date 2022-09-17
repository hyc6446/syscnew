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
use addons\shopro\model\PriorityBuy;
use addons\shopro\model\TradeOrder;
use addons\shopro\model\UserWalletLog;
use addons\shopro\model\GoodsSkuPrice;
use addons\shopro\controller\Bank as BankModel;
use think\Db;
use think\Log;
use think\Model;

class Pay extends Base
{

    protected $noNeedLogin = ['prepay', 'notifyx', 'notifyr', 'notifjuhe','notifbank', 'alipay','tessst','verify','publicKey'];
    protected $noNeedRight = ['*'];
    private $url = 'https://api.mch.weixin.qq.com/pay/unifiedorder';
    private $Appid = 'wx2b13664ebc6aba26';//appid
    private $Mchid = '1493366952';//商户号
    private $WxKey = 's1iazzdw1yndve5dbevvyb5nq0fovbth';//签名秘钥
    
    //微信支付-旧版
    public function wxPayOrder(){
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
            $notify_url = $this->request->root(true) . '/addons/shopro/pay/notifjuhe/platform/H5/order_type/box_order';//回调地址
            $goods_title = urlencode("盲盒购买");//内容
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
            $notify_url = $this->request->root(true) . '/addons/shopro/pay/notifjuhe/platform/H5';//回调地址
            $goods_title = urlencode("藏品购买");//内容
        }
        $appid = $this->Appid;
        $mch_id = $this->Mchid;
        $key = $this->WxKey;
        $userip = "47.110.67.71";     //获得用户设备IP
        $out_trade_no = $order_sn;//平台内部订单号
        $nonce_str = $this->getNonceStr(32);
        $body = "引力数字支付";//内容
        $total_fee = $order['total_fee'] * 100; //金额
        // $total_fee = 1; //金额
        $trade_type = 'MWEB';//交易类型 具体看API 里面有详细介绍
        $timeStamp = time();
        $timeStamp = "$timeStamp";
        // $scene_info = "{'h5_info': {'type':'Wap','wap_url': 'https://pay.qq.com','wap_name': '腾讯充值'}}";
        $scene_info = "{'h5_info': {'type':'Wap'}}";
        $signA ="appid=$appid&body=$body&mch_id=$mch_id&nonce_str=$nonce_str&notify_url=$notify_url&out_trade_no=$out_trade_no&scene_info=$scene_info&spbill_create_ip=$userip&total_fee=$total_fee&trade_type=$trade_type";
        $strSignTmp = $signA."&key=$key"; //拼接字符串  注意顺序微信有个测试网址 顺序按照他的来 直接点下面的校正测试 包括下面XML  是否正确
        $sign = strtoupper(MD5($strSignTmp)); // MD5 后转换成大写
        $post_data = "<xml>
                    <appid>$appid</appid>
                    <body>$body</body>
                    <mch_id>$mch_id</mch_id>
                    <nonce_str>$nonce_str</nonce_str>
                    <notify_url>$notify_url</notify_url>
                    <out_trade_no>$out_trade_no</out_trade_no>
                    <scene_info>$scene_info</scene_info>
                    <spbill_create_ip>$userip</spbill_create_ip>
                    <total_fee>$total_fee</total_fee>
                    <trade_type>$trade_type</trade_type>
                    <sign>$sign</sign>
            </xml>";//拼接成XML格式
        $url = $this->url;//微信传参地址
        $dataxml = $this->postXmlCurl($post_data,$url); //后台POST微信传参地址  同时取得微信返回的参数
        $objectxml = (array)simplexml_load_string($dataxml, 'SimpleXMLElement', LIBXML_NOCDATA); //将微信返回的XML 转换成数组
        // var_dump($objectxml);exit;
        if($objectxml['return_code']=="SUCCESS"&&$objectxml['result_code']=="SUCCESS"){
            $this->success('请求成功', $objectxml['mweb_url']);
        }else{
            $this->error($dataxml['err_code_des']);
        }
    }
    
    //藏品支付-杉德
    public function bankBandPay(){
        $order_id = input('order_id');
        $bank_id= input('bank_id/d');
        $code= input('code');
        $order_type= input('order_type');//product：藏品支付  topup：充值
        //验证支付密码
        $password = input('password/d');
        $result =$this->auth->checkpwd(trim($password));
        if(!$result){
            $this->error('支付密码不正确');
        }
        $bank = new BankModel();
        $bank->bankPay($order_id,$bank_id,$code,$order_type);
    }

    //随机字符串
    public function getNonceStr($length=32){
        $chars = 'abcdefghijklmnopqrstuvwxyz0123456789';
        $str = '';
        for($i = 0; $i<$length; $i++){
            $str .= substr($chars, mt_rand(0, strlen($chars)-1), 1);
        }
        return $str;
    }

    //微信支付下单
    public function postXmlCurl($xml,$url,$second = 30){
    //    $headers = [
    //        'Accept: text/xml, text/plain, application/x-gzip',
    //        'Content-Type: text/xml; charset=utf-8',
    //    ];
        $ch = curl_init();
        //设置超时
        curl_setopt($ch, CURLOPT_TIMEOUT, $second);
        curl_setopt($ch,CURLOPT_URL, $url);
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,FALSE);
        curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,FALSE);
        //设置header
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        //要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        //post提交方式
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
    //    curl_setopt($ch,CURLOPT_HTTPHEADER, $headers);
        //运行curl
        $data = curl_exec($ch);
        //返回结果
        if($data){
            curl_close($ch);
            return $data;
        }else{
            $error = curl_errno($ch);
            curl_close($ch);
            return "curl出错，错误码:$error"."<br>";
        }
    }
    
    //生成签名
    public function makeSign($params){
        //签名步骤一：按字典序排序参数
        ksort($params);
        $string = $this->ToUrlParams();
        //签名步骤二：在string后加入KEY
        $string = $string . '&key=' . $this->WxKey;
        //签名步骤三：MD5加密
        $string = md5($string);
        //签名步骤四：所有字符转为大写
        $result = strtoupper($string);
//        halt($result);
        return $result;
    }
    
    //格式化参数格式化成url参数
    public function ToUrlParams(){
        $buff = '';
        foreach($this->params as $k => $v)
        {
            if($k!='sign' && $v!='' && !is_array($v)){
            $buff .= $k . '=' . $v . '&';
            }
        }
        $buff = trim($buff, '&');
        return $buff;
    }
    
    //将xml转成array
    public function FromXml($xml){
        if(!$xml){
        throw new Exception('xml数据异常！');
        }
        //将XML转为array
        //禁止引用外部xml实体
        libxml_disable_entity_loader(true);
        $this->params = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        return $this->params;
    }
    
    /**
     * 微信支付成功回调
     */
    public function notifjuhe()
    {
        
        $data = $this->request->param();
        //获取xml
        $xml = file_get_contents('php://input');
       
        //转成php数组
        $params = $this->FromXml($xml);
        //保存原sign
        $data_sign = $params['sign'];
        
        //sign不参与签名
        unset($params['sign']);
        
        $sign = $this->makeSign($params);
        // file_put_contents(ROOT_PATH.'wxPay.txt','【'.date('Y-m-d H:i:s').'】'.$data_sign.'&nbsp;&nbsp;&nbsp;'.$sign,FILE_APPEND);
        //判断签名是否正确 判断支付状态
        if ($sign==$data_sign && $this->params['return_code']=='SUCCESS' && $this->params['result_code']=='SUCCESS') {
             
        // file_put_contents(ROOT_PATH.'wxPay.txt','【'.date('Y-m-d H:i:s').'】'.json_encode($this->params),FILE_APPEND);
            try {
                
                if(isset($data['order_type'])){
    
                    // 支付成功流程
                    
                    $box_order = new BoxOrder();
                    $order = $box_order->where('out_trade_no', $params['out_trade_no'])->find();
                    $pay_fee = $params['total_fee'] / 100;
                    $box_model = new \addons\shopro\model\Box();
                    if (!$order || $order->status !='unpay') {
                        // 订单不存在，或者订单已支付
                        return "SUCCESS";
                    }
                    //执行订单更新操作
                    $notify = [
                        'out_trade_no' => $params['out_trade_no'],
                        'transaction_id' => $params['transaction_id'],
                        'pay_time' => time(),
                        'pay_rmb' => $pay_fee,
                        'pay_coin' => $pay_fee,
                        'status' => 'unused',
                        'backend_read' => 0,
                        'pay_method' => "wechat"              // 支付方式
                    ];
                    $order->save($notify);
                    // file_put_contents(ROOT_PATH.'wxPay.txt','【'.date('Y-m-d H:i:s').'】'.$order->box_id,FILE_APPEND);
                    // 扣除库存;
                    $box_model->where('id',$order->box_id)->setDec('sales_num',$order->num);
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
                            if($p_user->share_count%3==0 && $p_user->share_count<61){
                                $p_user->box_num +=1;
                            }
                            $p_user->save();
                        }
                    }
                    return "SUCCESS";
                }else{
                    
                    list($order, $prepay_type) = $this->getOrderInstance($params['out_trade_no']);
                    // 判断支付宝微信是否是支付成功状态，如果不是，直接返回响应
                    // 支付成功流程
                    $pay_fee = $params['total_fee'] / 100;
                    
                    //你可以在此编写订单逻辑
                    $order = $order->where('order_sn', $params['out_trade_no'])->find();
                    
    
                    if (!$order || $order->status > 0) {
                        // 订单不存在，或者订单已支付
                        return "SUCCESS";
                    }
    
                    Db::transaction(function () use ($order, $params, $pay_fee) {
                        $notify = [
                            'order_sn' => $params['out_trade_no'],
                            'transaction_id' => $params['transaction_id'],
                            'notify_time' => time(),
                            'buyer_email' => '',
                            'payment_json' => json_encode($params),
                            'pay_fee' => $pay_fee,
                            'pay_type' => 'wechat'              // 支付方式
                        ];
    // file_put_contents(ROOT_PATH.'wxPay.txt','【'.date('Y-m-d H:i:s').'】'.json_encode($order),FILE_APPEND);
                        $order->paymentProcess($order, $notify);
    
                        //销毁
                        if($order['type']=="goods"){
                            
                            $orderItem = OrderItem::get(['order_id' => $order['id']]);
                            $sku = GoodsSkuPrice::where('goods_id', $orderItem['goods_id'])->field('sales')->find();
                            $data = [
                                'user_id' => $order['user_id'],
                                'goods_id' => $orderItem['goods_id'],
                                'original_price' => $params['total_fee'],
                                'type' => 1,
                                'sn' => $sku['sales'] + 1,
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
                        }
                        
                    });
                    return "SUCCESS";
                }
            } catch (\Exception $e) {
                Log::write('notifyx-error:' . json_encode($e->getMessage()));
                $error = json_encode([
                    'a' => $e->getLine(),
                    'b' => $e->getFile(),
                    'c' => $e->getTrace(),
                    'd' => $e->getMessage()
                ], JSON_UNESCAPED_UNICODE);
                \think\Log::error('notifyx-' . get_class() . '-ssssssss' . '：执行失败，错误信息：' . $error);
            }
        }else{
            return "failure";
        }
    }
    
    // 公钥验签
    public function verify($plainText, $sign)
    {
        $resource = openssl_pkey_get_public($this->publicKey());
        $result   = openssl_verify($plainText, base64_decode($sign), $resource);
        openssl_free_key($resource);
        if (!$result) {
            throw new \Exception('签名验证未通过,plainText:' . $plainText . '。sign:' . $sign);
        }
        return $result;
    }

    // 公钥
    private function publicKey()
    {
        try {
            $file = file_get_contents("/www/wwwroot/nft/addons/shopro/cert/sand.cer");
            if (!$file) {
                throw new \Exception('getPublicKey::file_get_contents ERROR 公钥文件读取有误,config文件夹中进行修改');
            }
            $cert   = chunk_split(base64_encode($file), 64, "\n");
            $cert   = "-----BEGIN CERTIFICATE-----\n" . $cert . "-----END CERTIFICATE-----\n";
            $res    = openssl_pkey_get_public($cert);
            $detail = openssl_pkey_get_details($res);
            openssl_free_key($res);
            if (!$detail) {
                throw new \Exception('getPublicKey::openssl_pkey_get_details ERROR 公钥文件解析有误');
            }
            return $detail['key'];
        } catch (\Exception $e) {
            throw $e;
        }
    }
    
    /**
     * 杉德支付成功回调
     */
    public function notifbank()
    {
        $data = $_POST['data'];
        $sign = $_POST['sign'];
        $resData = $this->request->param();
        //验签
        $verifyFlag = $this->verify($data, $sign);
        $data = json_decode($data,true);
        if($verifyFlag &&  $data['head']['respCode']=='000000'){
            $result['respCode'] = '000000';
            $result = json_encode($result);
            $params = $data['body'];
            try {
                if(isset($resData['order_type'])&&$resData['order_type']=='box_order'){
                    // 支付成功流程
                    $box_order = new BoxOrder();
                    $order = $box_order->where('out_trade_no', $params['orderCode'])->find();
                    $pay_fee = $params['totalAmount'] / 100;
                    $box_model = new \addons\shopro\model\Box();
                    if (!$order || $order->status !='unpay') {
                        // 订单不存在，或者订单已支付
                        return $result;
                    }
                    //执行订单更新操作
                    $notify = [
                        'out_trade_no' => $params['orderCode'],
                        'transaction_id' => $params['tradeNo'],
                        'pay_time' => time(),
                        'pay_rmb' => $pay_fee,
                        'pay_coin' => $pay_fee,
                        'status' => 'unused',
                        'backend_read' => 0,
                        'pay_method' => "bankpay"              // 支付方式
                    ];
                    $order->save($notify);
                    // file_put_contents(ROOT_PATH.'wxPay.txt','【'.date('Y-m-d H:i:s').'】'.$order->box_id,FILE_APPEND);
                    // 扣除库存;
                    $box_model->where('id',$order->box_id)->setDec('sales_num',$order->num);
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
                            // if($p_user->share_count%3==0 && $p_user->share_count<61){
                            //     $p_user->box_num +=1;
                            // }
                            $p_user->save();
                        }
                    }
                    return $result;
                }elseif(isset($resData['order_type'])&&$resData['order_type']=='top_order'){
                    // 支付成功流程
                    $order = TradeOrder::where('order_sn', $params['orderCode'])->find();
                    $pay_fee = $params['totalAmount'] / 100;
                    if (!$order || $order['status'] > 0) {
                        // 订单不存在，或者订单已支付
                        return $result;
                    }
                    //执行订单更新操作
                    $notify = [
                        'transaction_id' => $params['tradeNo'],
                        'paytime' => time(),
                        'pay_fee' => $pay_fee,
                        'status' => 1,
                        'pay_type' => "bankpay"              // 支付方式
                    ];
                    TradeOrder::update($notify,['id'=>$order['id']]);
                    // file_put_contents(ROOT_PATH.'wxPay.txt','【'.date('Y-m-d H:i:s').'】'.$order->box_id,FILE_APPEND);
                    // 增加余额;
                    $user = User::get(['id'=>$order['user_id']]);
                    $money = $user['money'] + $pay_fee;
                    User::update(['money'=>$money],['id'=>$order['user_id']]);
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
//                            if($p_user->share_count%3==0 && $p_user->share_count<61){
//                                $p_user->box_num +=1;
//                            }
                            $p_user->save();
                        }
                    }
                    //添加余额变动记录
                    $wallet['user_id'] = $order['user_id'];
                    $wallet['wallet'] = "+".$pay_fee;
                    $wallet['wallet_type'] = "money";
                    $wallet['type'] = "topup";
                    $wallet['before'] = $user['money'];
                    $wallet['after'] = $money;
                    $wallet['item_id'] = $order['id'];
                    $wallet['memo'] = "余额充值";
                    $wallet['oper_type'] = "user";
                    $wallet['oper_id'] = $order['user_id'];
                    UserWalletLog::create($wallet);
                    return $result;
                }else{
                    list($order, $prepay_type) = $this->getOrderInstance($params['orderCode']);
                    // 支付成功流程
                    $pay_fee = $params['totalAmount'] / 100;
                    //你可以在此编写订单逻辑
                    $order = $order->where('order_sn', $params['orderCode'])->find();
                    if (!$order || $order->status > 0) {
                        // 订单不存在，或者订单已支付
                        return $result;
                    }
                    Db::transaction(function () use ($order, $params, $pay_fee) {
                        $notify = [
                            'order_sn' => $params['orderCode'],
                            'transaction_id' => $params['tradeNo'],
                            'notify_time' => time(),
                            'buyer_email' => '',
                            'payment_json' => json_encode($params),
                            'pay_fee' => $pay_fee,
                            'pay_type' => 'bankpay'              // 支付方式
                        ];
                        // file_put_contents(ROOT_PATH.'wxPay.txt','【'.date('Y-m-d H:i:s').'】'.json_encode($order),FILE_APPEND);
                        $order->paymentProcess($order, $notify);
                        //销毁
                        if($order['type']=="goods"){
                            $orderItem = OrderItem::get(['order_id' => $order['id']]);
                            $sku = GoodsSkuPrice::where('goods_id', $orderItem['goods_id'])->field('sales')->find();
                            $data = [
                                'user_id' => $order['user_id'],
                                'goods_id' => $orderItem['goods_id'],
                                'original_price' => $params['totalAmount'],
                                'type' => 1,
                                'sn' => $sku['sales'] + 1,
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
                        }

                    });
                    return $result;
                }
            } catch (\Exception $e) {
                Log::write('notifyx-error:' . json_encode($e->getMessage()));
                $error = json_encode([
                    'a' => $e->getLine(),
                    'b' => $e->getFile(),
                    'c' => $e->getTrace(),
                    'd' => $e->getMessage()
                ], JSON_UNESCAPED_UNICODE);
                \think\Log::error('notifyx-' . get_class() . '-ssssssss' . '：执行失败，错误信息：' . $error);
            }
        }else{
            return "failure";
        }
    }

    /**
     * 支付宝网页支付
     */
    public function alipay()
    {
        $order_sn = $this->request->get('order_sn');

        $order_type = input('order_type');
        if (isset($order_type) && $order_type == 'boxOrder') {
            $box_order = new BoxOrder();
            $order = $box_order->field('id,out_trade_no as order_sn, rmb_amount as total_fee ,status')->where('out_trade_no', $order_sn)->find();

            if (!$order) {
                throw new \Exception("订单不存在");
            }
            if ('unpay' != $order->status) {
                throw new \Exception("该订单已支付，请勿重复支付");
            }
            $notify_url = $this->request->root(true) . '/addons/shopro/pay/notifyx/payment/alipay/platform/H5/order_type/box_order';
        } else {
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
        
        // var_dump($order);exit;
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
        if ($order['activity_type']!= "priority" && (int)$shoproConfig['goods_limit'] > 0) {
            //限购
            $goods_id =  \addons\shopro\model\OrderItem::where(['id'=>$order->id])->value('goods_id');
            $order_ids = \addons\shopro\model\OrderItem::where(['goods_id'=>$goods_id,'user_id'=>$this->auth->id])->column('order_id');
            $count = \addons\shopro\model\Order::where(['id'=>['in',$order_ids],'status'=>['>',0]])->count();
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
            // if (in_array($platform, ['wxOfficialAccount', 'wxMiniProgram'])) {
            //     if (isset($openid) && $openid) {
            //         // 如果传的有 openid
            //         $order_data['openid'] = $openid;
            //     } else {
            //         // 没有 openid 默认拿下单人的 openid
            //         $oauth = \addons\shopro\model\UserOauth::where([
            //             'user_id' => $order->user_id,
            //             'provider' => 'Wechat',
            //             'platform' => $platform
            //         ])->find();
        
            //         $order_data['openid'] = $oauth ? $oauth->openid : '';
            //     }
    
            //     if (empty($order_data['openid'])) {
            //         // 缺少 openid
            //         return $this->success('缺少 openid', 'no_openid');
            //     }
            // }

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
        // if ($platform == 'H5' && $payment == 'wechat') {
        //     $result = $result->getContent();
        // }
        Log::info("prepay99");
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

            // 处理优先购
            if ($orderItem->activity_type == 'priority') {
                PriorityBuy::editNum($user->id, $orderItem->goods_num);
            }
            $data = [
                'user_id' => $order['user_id'],
                'original_price' => $order->total_fee,
                'goods_id' => $orderItem['goods_id'],
                'type' => 1,
                'status' => 0,
                'orderItem' => $orderItem,
                'order_sn' => $order['order_sn'],
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
                //增加寄售用户的余额
                User::money($total_fee, $collect->user_id, 'wallet_pay', $order->id, '',[
                    'order_id' => $order->id,
                    'order_sn' => $order->order_sn,
                ]);
            }

            if ($orderItem['goods_id']) {
                //购买 todo:上链
                $res = \addons\shopro\model\UserCollect::edit($data);
            }

            $order->paymentProcess($order, $notify);
            // Log::info("walletPay11",json_encode($order));
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
        $order_type = isset($order_type) ? $this->request->param('order_type') : '';
        file_put_contents(ROOT_PATH . 'wxPay.txt', '【' . date('Y-m-d H:i:s') . '】' . $order_type, FILE_APPEND);
        $pay = new \addons\shopro\library\PayService($payment, $platform);

        $result = $pay->notify(function ($data, $pay) use ($payment, $platform, $order_type) {
            Log::write('notifyx-result:' . json_encode($data));
            try {
                $out_trade_no = $data['out_trade_no'];
                $out_refund_no = $data['out_biz_no'] ?? '';

                if ($order_type) {

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
                    //执行订单更新操作
                    $notify = [
                        'out_trade_no' => $data['out_trade_no'],
                        'transaction_id' => $payment == 'wechat' ? $data['transaction_id'] :'',
                        'alipay_trade_no' => $payment == 'alipay' ? $data['trade_no'] : '',
                        'pay_time' => strtotime($data['gmt_payment']),
//                        'buyer_email' => $payment == 'alipay' ? $data['buyer_logon_id'] : $data['openid'],
//                        'payment_json' => json_encode($data->all()),
                        'pay_rmb' => $pay_fee,
                        'pay_coin' => $pay_fee,
                        'status' => 'unused',
                        'backend_read' => 0,
                        'pay_method' => $payment              // 支付方式
                    ];

                    $order->save($notify);

                    // 扣除库存;
                    $box_model->where('id',$order->box_id)->setDec('sales_num',$order->num);

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

                        $order->paymentProcess($order, $notify);
                        
                          //销毁
                        if($order['type']=="goods"){
                            $orderItem = OrderItem::get(['order_id' => $order['id']]);
                            $sku = GoodsSkuPrice::where('goods_id', $orderItem['goods_id'])->field('sales')->find();
                            $data = [
                                'user_id' => $order['user_id'],
                                'goods_id' => $orderItem['goods_id'],
                                'original_price' => $payment == 'alipay' ? $data['total_amount'] : $data['total_fee'],
                                'type' => 1,
                                'sn' => $sku['sales'] + 1,
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
                        }

                    });

                    return $pay->success()->send();
                }


            } catch (\Exception $e) {
                Log::write('notifyx-error:' . json_encode($e->getMessage()));
                $error = json_encode([
                    'a' => $e->getLine(),
                    'b' => $e->getFile(),
                    'c' => $e->getTrace(),
                    'd' => $e->getMessage()
                ], JSON_UNESCAPED_UNICODE);
                \think\Log::error('notifyx-' . get_class() . '-ssssssss' . '：执行失败，错误信息：' . $error);
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
        $res = $this->auth->checkpwd(trim($pwd));
        if ($res) {
            $this->success('校验成功');
        } else {
            $this->error($this->auth->getError());
        }
    }
    
//
//    public function tessst()
//    {
//        Log::write('notifyx-comein:');
//
//        $payment = $this->request->param('payment');
//        $platform = $this->request->param('platform');
//        $order_type = $this->request->param('order_type','box');
//        $order_type = isset($order_type)?$this->request->param('order_type'):'';
//
//
//        $pay = new \addons\shopro\library\PayService($payment, $platform);
//
////        $result = $pay->notify(function ($data, $pay) use ($payment, $platform,$order_type) {
//            $data = json_decode('{"gmt_create":"2022-07-14 00:40:03","charset":"utf-8","seller_email":"1065113497@qq.com","subject":"\u5546\u57ce\u8ba2\u5355\u652f\u4ed8","sign":"Fedq7sdmJRGXqGLFWJqlr64hLwjFrFfUUpyffqcbNjG5BE1OmfRqtKNkztCJJyF7\/EC2oSwX9087wLZnW7\/qBF+362QCQv0MgG8YKwF3KmzSZXfXY\/337nTGyGzkaluIjFYDSRkZ5wwHsY7k3eMpgi94g0Crzb7IvsLLYKobQzGOdaaB29pUHrSidVmxJweVQH01rAKERrKw9voBNR+1MwEsrpvIUuKzMH4psgdnhltnwaDFn0HalDWG0XPEqbCJqaTRV7sInLI2LqGewzJYheOzdEt9uUnlsFnbMyW8bkLBxLJ5xP18ORq22N\/SHNdK0K37UQPnEBG2bv560Jyh5Q==","buyer_id":"2088132681931699","invoice_amount":"6.66","notify_id":"2022071400222004004031691417431640","fund_bill_list":"[{\"amount\":\"6.66\",\"fundChannel\":\"ALIPAYACCOUNT\"}]","notify_type":"trade_status_sync","trade_status":"TRADE_SUCCESS","receipt_amount":"6.66","buyer_pay_amount":"6.66","app_id":"2021003136625537","sign_type":"RSA2","seller_id":"2088441607902292","gmt_payment":"2022-07-14 00:40:03","notify_time":"2022-07-14 00:40:04","version":"1.0","out_trade_no":"202207140039422715317","total_amount":"6.66","trade_no":"2022071422001431691453117571","auth_app_id":"2021003136625537","buyer_logon_id":"183****7965","point_amount":"0.00"}',true);
//            Log::write('notifyx-result:'. json_encode($data));
//
//            try {
//                $out_trade_no = $data['out_trade_no'];
//                $out_refund_no = $data['out_biz_no'] ?? '';
//
//                if($order_type){
//
//                    // 判断支付宝微信是否是支付成功状态，如果不是，直接返回响应
//                    if ($payment == 'alipay' && $data['trade_status'] != 'TRADE_SUCCESS') {
//                        // 不是交易成功的通知，直接返回成功
//                        return $pay->success()->send();
//                    }
//                    if ($payment == 'wechat' && ($data['result_code'] != 'SUCCESS' || $data['return_code'] != 'SUCCESS')) {
//                        // 微信交易未成功，返回 false，让微信再次通知
//                        return false;
//                    }
//
//                    // 支付成功流程
//                    $pay_fee = $payment == 'alipay' ? $data['total_amount'] : $data['total_fee'] / 100;
//
//                    $box_order = new BoxOrder();
//
//                    $order = $box_order->where('out_trade_no', $out_trade_no)->find();
//
//                    $box_model = new \addons\shopro\model\Box();
//
////                    if (!$order || $order->status !='unpay') {
////                        // 订单不存在，或者订单已支付
////                        return $pay->success()->send();
////                    }
//                    //执行订单更新操作
//                    $notify = [
//                        'out_trade_no' => $data['out_trade_no'],
//                        'transaction_id' => $payment == 'wechat' ? $data['transaction_id'] :'',
//                        'alipay_trade_no' => $payment == 'alipay' ? $data['trade_no'] : '',
//                        'pay_time' => strtotime($data['gmt_payment']),
////                        'buyer_email' => $payment == 'alipay' ? $data['buyer_logon_id'] : $data['openid'],
////                        'payment_json' => json_encode($data->all()),
//                        'pay_rmb' => $pay_fee,
//                        'pay_coin' => $pay_fee,
//                        'status' => 'unused',
//                        'backend_read' => 0,
//                        'pay_method' => $payment              // 支付方式
//                    ];
//
////                    $order->save($notify);
////
////                    // 扣除库存;
//                    $box_model->where('id',$order->box_id)->setDec('sales_num',$order->num);
//
//                    $user = User::get(['id'=>$order->user_id]);
//                    //查询user的父级ID,新增一个推荐
//                    if($user->parent_user_id>0){
//                        $shareModel = new \addons\shopro\model\Share();
//                        $share = $shareModel->where(['user_id'=>$user->id,'share_id'=>$user->parent_user_id])->find();
//                        if(!$share){
//                            $shareData = [
//                                'user_id'=>$user->id,
//                                'share_id'=>$user->parent_user_id
//                            ];
//                            $shareModel->insert($shareData);
//
//                            //查询父级用户,并且增加推广人数
//                            // 查询一下 分享人ID 是否存在,不存在就+1
//                            $p_user = \addons\shopro\model\User::where(['id'=>$user->parent_user_id])->find();
//                            $p_user->curr_share_count +=1;
//                            $p_user->share_count +=1;
//                            $p_user->save();
//                        }
//                    }
//
//                    return $pay->success()->send();
//                }else{
//
//
//                    list($order, $prepay_type) = $this->getOrderInstance($out_trade_no);
//
//                    // 判断是否是支付宝退款（支付宝退款成功会通知该接口）
//                    if ($payment == 'alipay'    // 支付宝支付
//                        && $data['notify_type'] == 'trade_status_sync'      // 同步交易状态
//                        && $data['trade_status'] == 'TRADE_CLOSED'          // 交易关闭
//                        && $out_refund_no                                   // 退款单号
//                    ) {
//                        // 退款回调
//                        if ($prepay_type == 'order') {
//                            // 退款回调
//                            $this->refundFinish($out_trade_no, $out_refund_no);
//                        } else {
//                            // 其他订单如果支持退款，逻辑这里补充
//                        }
//
//                        return $pay->success()->send();
//                    }
//
//
//                    // 判断支付宝微信是否是支付成功状态，如果不是，直接返回响应
//                    if ($payment == 'alipay' && $data['trade_status'] != 'TRADE_SUCCESS') {
//                        // 不是交易成功的通知，直接返回成功
//                        return $pay->success()->send();
//                    }
//                    if ($payment == 'wechat' && ($data['result_code'] != 'SUCCESS' || $data['return_code'] != 'SUCCESS')) {
//                        // 微信交易未成功，返回 false，让微信再次通知
//                        return false;
//                    }
//
//                    // 支付成功流程
//                    $pay_fee = $payment == 'alipay' ? $data['total_amount'] : $data['total_fee'] / 100;
//
//                    //你可以在此编写订单逻辑
//                    $order = $order->where('order_sn', $out_trade_no)->find();
//
//                    if (!$order || $order->status > 0) {
//                        // 订单不存在，或者订单已支付
//                        return $pay->success()->send();
//                    }
//
//                    Db::transaction(function () use ($order, $data, $payment, $platform, $pay_fee, $prepay_type) {
//                        $notify = [
//                            'order_sn' => $data['out_trade_no'],
//                            'transaction_id' => $payment == 'alipay' ? $data['trade_no'] : $data['transaction_id'],
//                            'notify_time' => date('Y-m-d H:i:s', strtotime($data['time_end'])),
//                            'buyer_email' => $payment == 'alipay' ? $data['buyer_logon_id'] : $data['openid'],
//                            'payment_json' => json_encode($data->all()),
//                            'pay_fee' => $pay_fee,
//                            'pay_type' => $payment              // 支付方式
//                        ];
//                        //销毁
//                        $orderItem = OrderItem::get(['order_id' => $order['id']]);
//                        $data = [
//                            'user_id' => $order['user_id'],
//                            'goods_id' => $orderItem['goods_id'],
//                            'original_price' => $payment == 'alipay' ? $data['total_amount'] : $data['total_fee'],
//                            'type' => 1,
//                            'status' => 0,
//                            'order_sn' => $data['out_trade_no'],
//                        ];
//                        $collectOrder = \addons\shopro\model\UserCollect::where('order_sn', $data['out_trade_no'])->find();
//                        if (!$collectOrder){
//                            if ($orderItem['user_collect_id']) {
//                                $collect = \addons\shopro\model\UserCollect::where('id', $orderItem['user_collect_id'])->find();
//                                $collect->is_consume = 1;//链上 资产是否销毁
//                                $collect->status = 2;
//                                $collect->status_time = time();
//                                $collect->save();
//                                //链上资产转移
////                                $res = \addons\shopro\model\UserCollect::transferShard($collect['asset_id'], $collect['user_id'], $order['user_id'], $collect['nft_id']);
////                                Log::info('购买寄售大厅链上资产转移:::' . json_encode($res));
//                                $data['notShard'] = 1;
//                                $data['asset_id'] = $collect['asset_id'];
//                                $data['sn'] = $collect['sn'];
//                                $data['operation_id'] = $res['data']['operation_id']??'';
//                                $data['task_id'] = $res['data']['task_id']??'';
//                            }
//                            if ($orderItem['goods_id']) {
//                                //购买 todo:上链
////                                $res = \addons\shopro\model\UserCollect::edit($data);
//                            }
//                        }
//
//
//                        $order->paymentProcess($order, $notify);
//                    });
//
//                    return $pay->success()->send();
//                }
//
//
//            } catch (\Exception $e) {
//                Log::write('notifyx-error:' . json_encode($e->getMessage()).'line:');
//                $error = json_encode([
//                    'a' => $e->getLine(),
//                    'b' => $e->getFile(),
//                    'c' => $e->getTrace(),
//                    'd' => $e->getMessage()
//                ], JSON_UNESCAPED_UNICODE);
//                \think\Log::error('notifyx-' . get_class() . '-ssssssss' . '：执行失败，错误信息：' . $error);
//            }
////        });
//
//        return true;
//    }
}
