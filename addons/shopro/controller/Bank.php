<?php

namespace addons\shopro\controller;

use addons\shopro\model\Bank as BandBank;
use addons\shopro\model\UserBank;
use addons\shopro\model\BoxOrder;
use addons\shopro\model\Order;
use addons\shopro\model\TradeOrder;
use think\Log;
use think\Db;
use think\Exception;

class Bank extends Base
{

    protected $noNeedLogin = ['bankPay', 'verify', 'publicKey'];
    protected $noNeedRight = ['*'];

    //申请绑卡
    public function applyBank()
    {
        // $this->error("系统维护中");
        $body['cardNo'] = input('card_no');
        $body['userName'] = input('username');
        $body['phoneNo'] = input('mobile');
        $body['certificateNo'] = input('cert_no');
        $body['userId'] = "xy" . $this->auth->id;
        $rand = $body['userId'] < 9999 ? mt_rand(100000, 99999999) : mt_rand(100, 99999);
        $body['applyNo'] = date('Yhis') . $rand;
        $body['creditFlag'] = 1;
        $body['certificateType'] = "01";
        //调用杉德接口
        $postData = $this->postData("sandPay.fastPay.apiPay.applyBindCard", $body);
        $url = "https://cashier.sandpay.com.cn/fastPay/apiPay/applyBindCard";
        // var_dump($postData);exit;
        $dataxml = $this->post_wx($url, $postData);
        if ($dataxml['head']['respCode'] == "000000") {
            //创建绑卡记录
            $data['user_id'] = $this->auth->id;
            $data['add_time'] = time();
            $data['order_no'] = $body['applyNo'];
            $data['card_no'] = $body['cardNo'];
            $data['username'] = $body['userName'];
            $data['mobile'] = $body['phoneNo'];
            $data['cert_no'] = $body['cardNo'];
            $data['sd_msg_no'] = $dataxml['body']['sdMsgNo'];
            $res = BandBank::create($data);
            // 创建提现绑卡记录
            $userBank['user_id'] = $this->auth->id;
            $userBank['type'] = 'bank';
            $userBank['real_name'] = $body['userName'];
            $userBank['bank_name'] = '银行卡用户';
            $userBank['card_no'] = $body['cardNo'];
            UserBank::create($userBank);
            if ($res) {
                $this->success('申请绑卡成功', $res);
            } else {
                $this->error("申请绑卡失败");
            }
        } else {
            Log::info($dataxml['head']);
            $this->error($dataxml['head']['respMsg']);
        }
    }

    //确认绑卡
    public function sureBank()
    {
        $bank_id = input('bank_id');
        $code = input('code');

        $order = BandBank::where(['id' => $bank_id, "user_id" => $this->auth->id])->field("mobile,sd_msg_no")->find();

        if (!$order) {
            $this->error("请先申请绑卡");
        }
        $body['userId'] = "xy" . $this->auth->id;
        $body['sdMsgNo'] = $order['sd_msg_no'];
        $body['phoneNo'] = $order['mobile'];
        $body['smsCode'] = $code;
        // var_dump($body['sdMsgNo']);exit;
        //调用杉德接口
        $postData = $this->postData("sandPay.fastPay.apiPay.confirmBindCard", $body);
        $url = "https://cashier.sandpay.com.cn/fastPay/apiPay/confirmBindCard";
        // var_dump($postData);exit;
        $dataxml = $this->post_wx($url, $postData);
        if ($dataxml['head']['respCode'] == "000000") {
            //修改绑卡记录
            $data['sd_bid'] = $dataxml['body']['bid'];
            $data['status'] = 1;
            $res = BandBank::update($data, ["id" => $bank_id]);
            if ($res) {
                $this->success('绑卡成功', $res);
            } else {
                $this->error("绑卡失败");
            }
        } else {
            $this->error($dataxml['head']['respMsg']);
        }
    }

    //发送支付短信 "050005"
    public function sendPaySms()
    {
        $bank_id = input('bank_id');
        $order_id = input('order_id');
        $order_type = input('order_type'); //box盲盒，product藏品，topup充值
        $bank = BandBank::where(['id' => $bank_id, "user_id" => $this->auth->id])->field("mobile,sd_bid")->find();
        if (!$bank) {
            $this->error("请先绑卡");
        }
        if ($order_type == "box") {
            $order = BoxOrder::field('out_trade_no as order_sn')->where('id', $order_id)->where('user_id', $this->auth->id)->find();
        } elseif ($order_type == "topup") {
            $order = TradeOrder::field('order_sn')->where('order_sn', $order_id)->where('user_id', $this->auth->id)->find();
        } else {
            $order = Order::field('order_sn')->where('id', $order_id)->where('user_id', $this->auth->id)->find();
        }
        $body['userId'] = $this->auth->id;
        $body['orderCode'] = $order['order_sn'];
        $body['phoneNo'] = $bank['mobile'];
        $body['bid'] = $bank['sd_bid'];
        //调用杉德接口
        $postData = $this->postData("sandPay.fastPay.common.sms", $body);
        $url = "https://cashier.sandpay.com.cn/fastPay/apiPay/sms";
        $dataxml = $this->post_wx($url, $postData);
        if ($dataxml['head']['respCode'] == "000000") {
            $this->success('发送成功', 1);
        } else {
            $this->error($dataxml['head']['respMsg']);
        }
    }

    //绑卡支付
    public function bankPay($order_id, $bank_id, $code, $order_type)
    {
        if ($order_type == 'box') {
            $box_order = new BoxOrder();
            $order = $box_order->field('id,out_trade_no as order_sn, rmb_amount as total_fee ,status')->where('id', $order_id)->find();
            if (!$order) {
                throw new \Exception("订单不存在");
            }
            if ('unpay' != $order->status) {
                throw new \Exception("该订单已支付，请勿重复支付");
            }
            $notify_url = $this->request->root(true) . '/addons/shopro/pay/notifbank/platform/H5/order_type/box_order'; //回调地址
            $goods_title = urlencode("盲盒购买"); //内容
        } elseif ($order_type == 'topup') { //充值
            $order = TradeOrder::where('order_sn', $order_id)->field('id,order_sn,total_amount as total_fee,status')->find();
            if (!$order) {
                throw new \Exception("订单不存在");
            }
            if ($order['status'] > 0) {
                throw new \Exception("订单已支付");
            }
            if ($order['status'] < 0) {
                throw new \Exception("订单已失效");
            }
            $notify_url = $this->request->root(true) . '/addons/shopro/pay/notifbank/platform/H5/order_type/top_order'; //回调地址
            $goods_title = urlencode("余额充值"); //内容
        } else {
            $order = Order::where('id', $order_id)->field('id,order_sn,total_fee,status')->find();
            if (!$order) {
                throw new \Exception("订单不存在");
            }
            if ($order['status'] > 0) {
                throw new \Exception("订单已支付");
            }
            if ($order['status'] < 0) {
                throw new \Exception("订单已失效");
            }
            $notify_url = $this->request->root(true) . '/addons/shopro/pay/notifbank/platform/H5'; //回调地址
            $goods_title = urlencode("藏品购买"); //内容
        }

        $bank = BandBank::where(['id' => $bank_id, "user_id" => $this->auth->id])->field("mobile,sd_bid")->find();
        if (!$bank) {
            $this->error("请先申请绑卡");
        }

        Db::startTrans();
        try {
            $body['userId'] = $this->auth->id;
            $body['orderCode'] = $order['order_sn'];
            $body['phoneNo'] = $bank['mobile'];
            $body['bid'] = $bank['sd_bid'];
            $body['smsCode'] = $code;
            $body['orderTime'] = date("YmdHis", time());
            $body['totalAmount'] = sprintf("%012d", $order['total_fee'] * 100);
            $body['subject'] = "杉德银行卡支付";
            $body['body'] = $goods_title;
            $body['currencyCode'] = "156";
            $body['clearCycle'] = "0";
            $body['notifyUrl'] = $notify_url;
            //调用杉德接口
            $postData = $this->postData("sandPay.fastPay.apiPay.pay", $body);
            $url = "https://cashier.sandpay.com.cn/fastPay/apiPay/pay";
            $dataxml = $this->post_wx($url, $postData);
        } catch (\Exception $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }
        Db::commit();
        if ($dataxml['head']['respCode'] == "000000") {
            $this->success('操作成功', 1);
        } else {
            $this->error($dataxml['head']['respMsg']);
        }
    }

    //绑卡列表
    public function bankList()
    {
        $order = BandBank::where(["user_id" => $this->auth->id, "status" => 1])->field("id,card_no")->select();
        if ($order) {
            $this->success('绑卡列表', $order);
        } else {
            $this->error("暂无数据");
        }
    }


    public function postData($method, $body)
    {
        $data = array(
            'head' => array(
                'version'     => '1.0',
                'method'      => $method,
                'productId'   => "00000018",
                'accessType'  => "1",
                'mid'         => "6888805045868",
                'channelType' => "07",
                'reqTime'     => date('YmdHis', time()),
            ),
            'body' => $body,
        );

        $postData = array(
            'charset'  => 'utf-8',
            'signType' => '01',
            'data'     => json_encode($data),
            'sign'     => $this->sign($data),
        );
        return $postData;
    }

    //以post方式提交xml到对应的接口url
    private static function post_wx($url, $post_data, $header = [])
    {
        $post_data = http_build_query($post_data);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        $result = urldecode(curl_exec($ch));
        $result = explode("&", $result);
        $result = json_decode(substr($result[3], 5), true);
        curl_close($ch);
        return $result;
    }

    // 私钥加签
    protected function sign($plainText)
    {
        $plainText = json_encode($plainText);
        try {
            $resource = openssl_pkey_get_private($this->privateKey());
            $result   = openssl_sign($plainText, $sign, $resource);
            openssl_free_key($resource);
            if (!$result) throw new \Exception('sign error');
            return base64_encode($sign);
        } catch (\Exception $e) {
            throw $e;
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
            $file = file_get_contents("E:\phpstudy_pro\WWW\syscnew\addons\shopro\cert\cert\sand.cer");
            // $file = file_get_contents("/www/wwwroot/nft/addons/shopro/cert/sand.cer");
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

    // 私钥
    private function privateKey()
    {
        try {
            $file = file_get_contents("E:\phpstudy_pro\WWW\syscnew\addons\shopro\cert\mid_new.pfx");
            // $file = file_get_contents("/www/wwwroot/nft/addons/shopro/cert/mid_new.pfx");
            if (!$file) {
                throw new \Exception('getPrivateKey::file_get_contents 私钥文件读取有误,config文件夹中进行修改');
            }
            if (!openssl_pkcs12_read($file, $cert, "xiangyi888")) {
                throw new \Exception('getPrivateKey::openssl_pkcs12_read ERROR 私钥密码错误，config文件夹中进行修改');
            }
            return $cert['pkey'];
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
