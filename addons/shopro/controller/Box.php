<?php


namespace addons\shopro\controller;


use addons\shopro\model\BoxOrder;
use addons\shopro\model\Detail;
use addons\shopro\model\Box as BoxModel;
use addons\shopro\model\UserCollect as CollectModel;
use addons\shopro\model\Goods;
use addons\shopro\model\GoodsSkuPrice;
use addons\shopro\model\PrizeRecord;
use addons\shopro\controller\Bank as BankModel;
use think\Db;
use think\Exception;

class Box extends Base
{
    protected $noNeedLogin = ['recommend','getUserCollectSn','boxDetail','getPrizeRecord','test','grantShardAll','getGoodsUsers','cancelNum'];
    protected $noNeedRight = ['*'];
    
    //统计藏品没有的编号
    public function getUserCollectSn()
    {
        $collect = CollectModel::where(['goods_id'=>45,'status'=>array("in",[0,1])])->field("sn")->select();
        $sn = [];
        foreach ($collect as $key => $value){
            $sn[] = $value['sn'];
        }
        $num = "";
        for ($i=1;$i<500;$i++){
            if(!in_array($i,$sn)){
                $num = $num.$i.",";
            }
        }
        $this->success('空投成功',$num);
    }
    
    //查询创世勋章的所有用户
    public function getGoodsUsers()
    {
        $collect = CollectModel::where(['goods_id'=>37,'status'=>0])->field("user_id")->group("user_id")->select();
        // $this->success('空投成功',$collect);
        $mobile = "";
        foreach ($collect as $key => $value){
            $user = \addons\shopro\model\User::where(['id'=>$value['user_id']])->field("mobile")->find();
            $mobile = $mobile.$user['mobile'].",";
        }
        $this->success('空投成功',$mobile);
    }
    
    //判断用户是否拥有创世
    public function selectGoodsHave()
    {
        $collect = CollectModel::where(['goods_id'=>37,'status'=>0,"user_id"=>$this->auth->id])->field("id")->find();
        if($collect){
            $result = 1;
        }else{
            $result = 0;
        }
        $this->success('空投成功',$result);
    }
    
    //清除编号重复藏品，并重新空投
    public function cancelNum()
    {
        $collect = CollectModel::where(['goods_id'=>37,'status'=>0])->group('sn')->order("sn asc")->select();
        $count = "";
        foreach ($collect as $key => $value){
            $res = CollectModel::where(['goods_id'=>37,'status'=>0,"sn"=>$value['sn'],"id"=>array("not in",[$value['id']])])->count();
            if($res>0){
                $count = $count.$value['sn'].",";
                // $num++;
            }
            // $count[] = $value['sn'];
        }
        var_dump($count);exit;
        $se = "";
        $num = 0;
        for($i=0;$i<631;$i++){
            if(!in_array($i,$count)){
                $se = $se.$i.",";
                $num++;
            }
        }
        var_dump($se);
        var_dump($num);
        exit;
        $this->success('空投成功',$count);
    }
    
    //全部空投
    public function grantShardAll()
    {
        $ids = input('ids/d');
        if (!$ids)$this->error('请选择藏品');
        $box = input('mobile');
        $user = explode(",",$box);
        // var_dump(count($user));exit;
        foreach ($user as $key => $value){
            $order = \addons\shopro\model\User::where(['mobile'=>$value])->field("id")->find();
            if($order){
                $sku = GoodsSkuPrice::where('goods_id', $ids)->field('sales')->find();
                //空投 todo:上链
                $res = \addons\shopro\model\UserCollect::edit([
                    'user_id' => $order['id'],
                    'goods_id' => $ids,
                    'type' => 5,
                    'status' => 0,
                    'sn' => $sku['sales'] + 1,
                    'is_hook'=>1,
                ]);
                if (!$res) {
                    $this->error('空投失败');
                }
    
                $goodsSkuPrice = GoodsSkuPrice::where('goods_id', $ids)->find();
                if ($goodsSkuPrice) {
                    $goodsSkuPrice->setDec('stock', 1);         // 减少库存
                    $goodsSkuPrice->setInc('sales', 1);         // 增加销量
                }
            }
        }
        $this->success('空投成功');
    }
    
    public function test()
    {
        $order = BoxOrder::where(['status'=>'unused', 'box_id'=>30])->order('id','asc')->limit(0,10)->select();

//        $order_ids = array_column($order,'id');

        // 根据订单查找对应的 盲盒开奖记录
        foreach ($order as $item){
            $result = PrizeRecord::where(['user_id'=>$item['user_id'],'order_id'=>$item['id']])->find();
            // 如果没有开奖记录,执行开盲盒
            if(empty($result)){
                $this->open($item);
            }else{
                continue;
            }
        }
    }
  

    public function recommend()
    {
        $pagesize = input('pagesize/d', 1);
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
            ->field('a.id box_id,a.box_name,a.coin_price,b.name cate_name,b.weigh,a.category_id,b.color,a.sales_num,a.start_time,a.end_time')
            ->join('shopro_box_category b','a.category_id=b.id')
            ->whereNotIn('a.id', $emptyBoxIds)
            ->order('sort', 'desc')
            ->where($where)
            ->paginate($pagesize, false, ['page' => $page])
            ->each(function ($item) {
                // 查询前6个商品图片
                // $firstGoods = Detail::where('box_id', $item->box_id)->order('weigh', 'desc')->limit(6)->column('goods_id');
                // $goods_images = Goods::whereIn('id', $firstGoods)->column('image');
                $goods_images = Detail::alias('a')
                    ->field('b.id,b.price,b.image,c.name as cate_name,c.color,a.rate')
                    ->join("shopro_goods b","b.id = a.goods_id")
                    ->join('shopro_category c','c.id=b.category_ids')
                    ->where('a.box_id', $item->box_id)
                    ->order('a.weigh', 'desc')
                    ->limit(0,2)
                    ->select();

                foreach ($goods_images as &$image) {
                    $image['image'] = cdnurl($image['image'], true);
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


        // 获取商品的分类
//        $where = ['status'=>'normal','type'=>['neq',1]];
//        $cates = \addons\shopro\model\Category::where($where)->field('id,name,color')->select();
//        $list['category'] = $cates;

         $this->success('查询成功', $list);
        
    }

    public function myBox()
    {
//        $status = input('status/d');
        $pagesize = input('pagesize/d', 10);
        $page = input('page/d', 1);
//        $statusList = [1 => 'bag', 2 => 'exchange'];

//        if (!isset($statusList[$status])) {
//            $this->error('状态有误');
//        }
//        $status = $statusList[$status];

        $order = 'prize.id desc';
//        if ('exchange' == $status) {
//            $order = 'exchange_time desc';
//        }

        $list = PrizeRecord::alias('prize')
            ->field('prize.id record_id,prize.goods_name,prize.goods_image,prize.create_time,prize.exchange_time')
            ->field('order.coin_price box_coin_price,order.rmb_price box_rmb_price,order.pay_method')
            ->join('shopro_box_order order', 'order.id = prize.order_id')
            ->where('prize.user_id', $this->auth->id)
            ->order($order)
            ->paginate($pagesize, false, ['page' => $page])
            ->each(function ($item){
                $item->goods_image = $item->goods_image ? cdnurl($item->goods_image, true) : '';

                $item->box_coin_price = intval($item->box_coin_price);
                $item->box_rmb_price = floatval($item->box_rmb_price);

                $item->time = date('Y-m-d H:i:s', $item->create_time);

                $item->hidden(['create_time', 'exchange_time']);
            });

        $this->success('查询成功', $list);
        
    }


    public function boxDetail()
    {
        $box_id = input('box_id/d');
        $cate_id = input('category/d');
        $pagesize = input('pagesize/d', 10);
        $page = input('page/d', 1);
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

        $where = ['a.box_id'=>$box_id];
        if(!empty($cate_id)){
            $where['b.category_ids']=$cate_id;
        }

        $Goods =  Detail::alias("a")
            ->join("shopro_goods b","b.id = a.goods_id")
            ->join('shopro_category c','b.category_ids=c.id')
            ->field("b.image,b.price,b.title,c.name,c.color,a.rate")
            ->where($where)
            ->order("a.weigh desc")
            ->paginate($pagesize, false, ['page' => $page]);

        foreach ($Goods as &$first) {
            $first->image = $first->image ? cdnurl($first->image, true) : $first->image;
            $first->price = round($first->price, 2);
            $first->hidden(['coin_price']);
        }


//        $box_banner_images = [];
//        $box_banner = [];
//        $box->box_banner_images = explode(',', $box->box_banner_images);
//        $banner_desc = $box->box_banner_images_desc;
//        $banner_desc = $banner_desc ? json_decode($banner_desc, true) : [];
//        foreach ($box->box_banner_images as $index => $image) {
//            $image = $image ? cdnurl($image, true) : '';
//            $box_banner_images[] = $image;
//            $box_banner[] = [
//                'desc' => $banner_desc[$index] ?? '',
//                'image' => $image
//            ];
//        }

//        $ret = [
//            'goodslist' => $Goods,
//        ];

        $this->success('查询成功', $Goods);
    }


    public function boxOrderList($params)
    {

        $page = $params['page'];
        $pagesize = $params['limit'];

        $order = BoxOrder::alias('a')
            ->field('a.id,a.box_id,a.box_name,a.pay_method,a.image,a.pay_rmb,a.create_time,a.pay_time,a.num,a.status,a.coin_amount,a.rmb_amount,a.out_trade_no,a.select,a.user_id,b.goods_id,b.goods_name,b.goods_image')
            ->join('shopro_prize_record b','b.order_id=a.id','left')
            ->where('a.user_id', $this->auth->id)
            ->group('a.id')
            ->paginate($pagesize, false, ['page' => $page])->toArray();

        $data = [];
        foreach ($order['data'] as $key => $item){
            if($item['status']=='unpay'){
                $status_code = 'nopay';
                $status_name = '待支付';
                $status = 0;
                $status_desc = '订单待支付';
                $btn = ["cancel", "pay"];
            }else if($item['status']=='unused'){
                $status_code = 'nosend';
                $status_name = '已支付';
                $status = 1;
                $status_desc = '订单已支付';
                $btn=[];
            }else if($item['status']=='used'){
                $status_code = 'used';
                $status_name = '已开奖';
                $status = 1;
                $status_desc = '订单已支付';
                $btn=[];
            }else{
                $status_code = 'used';
                $status = 1;
                $status_name = '已完成';
                $status_desc = '订单已支付';
                $btn=[];
            }

            $data[$key] = [
                "id"=>$item['id'],
                "type"=>'box',
                "order_sn"=> $item['out_trade_no'],
                "total_amount"=> $item['rmb_amount'],
                "status"=> $status,
                "pay_type"=> $item['pay_method'],
                "paytime"=> $item['pay_time'],
                "createtime"=> $item['create_time'],
                "ext"=> "",
                "activity_type"=>"",
                "item"=> [
                    [
                        "goods_num"=> 1,
                        "image"=>$item['goods_image']?$item['goods_image']:$item['image'],
                        "title"=> $item['goods_name']?$item['goods_name']:$item['box_name'],
                        "category"=> ""
                    ]
                ],
                "status_code"=>$status_code,
                "status_name"=> $status_name,
                "status_desc"=> $status_desc,
                "btns"=> $btn,
                "ext_arr"=>[
                    "buy_type"=> "alone",
                    "groupon_id"=> 0,
                    "expired_time"=> 1657529816,
                    "cancel_time"=> 1657637213
                ]
            ];

        }
        $order['data'] = $data;

        return $order;


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
        $this->error('已售罄');
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
        $box = BoxModel::field('id,box_name,box_banner_images,coin_price,sales_num,start_time,end_time')->where('id', $box_id)->lock()->find();
        if (empty($box)) {
            $this->error('选择的盲盒有误');
        }

        if($box->sales_num<$num){
            $this->error('盲盒已售罄');
        }

        $this->limitBuy($box_id);

        if($box->start_time>time()){
            $this->error('盲盒还未开始售卖');
        }

        if($box->end_time<time()){
            $this->error('盲盒售卖已结束');
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
                'order_sn' => $res->out_trade_no,
                'box_name' => $res->box_name,
                'images' => $goodsImages,
                'coin_amount' => $res->coin_amount,
                'rmb_amount' => $res->rmb_amount,
                'coin_not_enough' => !!(intval($this->auth->money) < intval($res->coin_amount)),
//                'alipay' => $this->request->domain() . '/api/alipay/boxpay/orderid/' . intval($res->id),
//                'wechat' => '/api/wechat/boxpay/orderid/' . intval($res->id),
            ];
        } catch (Exception $e) {
            Db::rollback();
            $this->error('创建订单失败');
        }
        Db::commit();

        $this->success('创建订单成功', $ret);
    }

    public function limitBuy($box_id)
    {

        $configModel = new \addons\shopro\model\Config;
        $config = $configModel->where('name', '=', 'shopro')->value('value');
        $shoproConfig = json_decode($config, true);
        if ((int)$shoproConfig['box_limit']>0){
            //限购
            $count = \addons\shopro\model\BoxOrder::where(['status'=>'used','user_id'=>$this->auth->id,'box_id'=>$box_id])->count();
            if ($count>=$shoproConfig['box_limit'])$this->error('相同盲盒每人只能限购'.$shoproConfig['box_limit'].'件');
        }
        return true;

    }

    /**
     * 支付订单
     */
    public function payOrder()
    {
        $order_id = input('order_id/d');
        $pay_method = input('pay_method/row','wallet');// wallet=金币,wechat=微信,alipay=支付宝,bankpay=绑卡支付'

        $platform = request()->header('platform');
        if (!$platform) $this->error("请确认平台信息");

        if (!$pay_method || !in_array($pay_method, ['wechat', 'alipay', 'wallet',"box_num","bankpay"])) {
            $this->error("支付类型不能为空");
        }
        $order_sn = input('order_sn');
        if(isset($order_id)){
            $order = BoxOrder::field('id,box_id,num,status,coin_amount,rmb_amount,out_trade_no,select,user_id')->lock(true)
                ->where('id', $order_id)
                ->where('user_id', $this->auth->id)
                ->find();
        }

        if($order_sn){
            $order = BoxOrder::field('id,box_id,num,status,coin_amount,rmb_amount,out_trade_no,select,user_id')->lock(true)
                ->where('out_trade_no', $order_sn)
                ->where('user_id', $this->auth->id)
                ->find();
        }


        if (empty($order)) {
            $this->error('订单不存在');
        }

        if ('unpay' != $order->status) {
            $this->error('该订单已支付，请勿重复支付');
        }

        $this->limitBuy($order['box_id']);

        if($pay_method == 'wallet'){
            // 查询用户余额
            if (($this->auth->money*100) < ($order->rmb_amount*100)) {
                $this->error('您的金币不足');
            }
            $this->coinPay($order->id,"coin");
        }

        if($pay_method == 'box_num'){
            // 查询用户余额
            if ($this->auth->box_num<1) {
                $this->error('您的盲盒购买次数不足');
            }
            $this->coinPay($order->id,"box_num");
            // var_dump($result);exit;
        }

        //绑卡支付
        if($pay_method == 'bankpay'){
            //验证支付密码
            $password = input('password/d');
            $bank_id= input('bank_id/d');
            $code= input('code');
            $password = $this->auth->getEncryptPassword($password,$this->auth->salt);
            if($password!=$this->auth->password){
                $this->error('支付密码不正确');
            }
            $bank = new BankModel();
            $bank->bankPay($order->id,$bank_id,$code,"box");
            // var_dump($res);exit;
        }
        
        $order_data = [
            'order_id' => $order->id,
            'out_trade_no' => $order->out_trade_no,
            'total_fee' => $order->rmb_amount,
        ];

        if($pay_method == 'wechat'){
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
            //         return $this->error('缺少 openid', 'no_openid');
            //     }
            // }
            $order_data['body'] = '商城订单支付';
        }

        if($pay_method == 'alipay'){
            $order_data['subject'] = '商城订单支付';
        }

        $notify_url = $this->request->root(true) . '/addons/shopro/pay/notifyx/payment/' . $pay_method . '/platform/' . $platform. '/order_type/box_order';
        $pay = new \addons\shopro\library\PayService($pay_method, $platform, $notify_url);
        // var_dump($pay);exit;
        try {
            $result = $pay->create($order_data,'boxOrder');
        } catch (\Yansongda\Pay\Exceptions\Exception $e) {
            $this->error("支付配置错误：" . $e->getMessage());
        }

        if ($platform == 'App') {
            $result = $result->getContent();
        }
        if ($platform == 'H5' && $pay_method == 'wechat') {
            $result = $result->getContent();
        }

         $this->success('获取预付款成功', [
            'pay_data' => $result,
            'pay_action' => $pay->method,
        ]);

    }


    public function coinPay($order_id,$pay_method)
    {
        $order = BoxOrder::field('id,box_id,num,status,coin_amount,rmb_amount,rmb_price,out_trade_no,select,user_id')->lock(true)
            ->where('id', $order_id)
            ->where('user_id', $this->auth->id)
            ->find();

        if($order->status == 'unused' || $order->status=='used'){
            $this->error("订单已支付，请勿重复支付" );
        }

        $box = \addons\shopro\model\Box::get($order->box_id);

        Db::startTrans();
        try {
            // 更新订单信息
            $order->pay_method = $pay_method;
            $order->pay_coin = $order->coin_amount;
            $order->pay_rmb = $order->rmb_price;
            $order->pay_time = time();
            $order->status = 'unused';// 状态:unpay=待支付,unused=待抽奖,used=已使用
            $order->backend_read = 0;
            $order->save();

            $coin_before = $this->auth->money;
            $user = $this->auth->getUser();
            if($pay_method=="coin"){
                // 减少金币余额
                
                // 创建余额支付记录
                \addons\shopro\model\User::money(-$order->pay_rmb, $user->id, 'box_pay', $order->id, '',[
                    'order_id' => $order->id,
                    'box_id' => $order->box_id,
                ]);
            }
            
            //扣除盲盒购买次数
            if($pay_method=="box_num"){
                $num = $user->box_num - 1;
                \addons\shopro\model\User::update(["box_num"=>$num],["id"=>$this->auth->id]);
            }

            // 支付成功，扣除库存
            $box->setDec('sales_num',$order->num);
            
            //查询user的父级ID,新增一个推荐
            // return $user;
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
                    // if($p_user->share_count%4==0 && $p_user->share_count<41){
                    //     $p_user->box_num +=1;
                    // }
                    $p_user->save();
                }
            }
            

        } catch (\Exception $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }

        Db::commit();

        // 开箱
//        $prize = $this->open($order);

        $this->success('支付成功', ['order' => $order]);
    }

    public function getSuccessData()
    {
        $order_id = input('order_id/row');

        $order = BoxOrder::field('id,box_id,num,status,coin_amount,rmb_amount,out_trade_no,select,user_id')->lock(true)
            ->where('id', $order_id)
            ->where('user_id', $this->auth->id)
            ->find();
        // var_dump($order_id);exit;
        if($order->status=='unused'){
            $prize = $this->open($order);
            $this->success('开盲盒成功',['prize'=>$prize]);
        }else if($order->status=='used'){
            //查看盲盒是否已经开了
            $prizeRecord = Prizerecord::where('order_id','=',$order->id)->select();
            //根据中奖记录查询商品分类
            $goodsIds = array_column($prizeRecord,'goods_id');
            $goodsInfo = [];
            foreach ($goodsIds as $goodsId) {
                $goodsInfo[] = Goods::alias('a')
                    ->field('a.*,b.name as cate_name,b.color')
                    ->join('shopro_category b','b.id=a.category_ids')
                    ->where('a.id', $goodsId)->find();
            }

            foreach ($goodsInfo as $key=> $goods){
                $prizeInfo[] = [
                    'prize_id' => intval($prizeRecord[$key]->id),
                    'image' => $prizeRecord[$key]->goods_image ? cdnurl($prizeRecord[$key]->goods_image, true) : '',
                    'goods_name' => $prizeRecord[$key]->goods_name,
                    'cate_name' =>$goods->cate_name,
                    'color' =>$goods->color,
                ];
            }

            $ret = [
                'select' => $order->select,
                'prizeInfo' => $prizeInfo
            ];
            $this->success('盲盒内容',['prize'=>$ret]);
        }else {
            $this->success('订单尚未支付');
        }

    }




    /**
     * 盲盒开奖
     * @param BoxOrder $order
     * @return array|bool
     */
    private function open(BoxOrder $order)
    {
        // 检查订单状态
        if ('unused' != $order->status) { // 状态:unpay=待支付,unused=待抽奖,used=已使用
            return false;
        }
        
        Db::startTrans();
        //执行订单更新操作
        $notify = ['status' => 'used'];
        $orderresult =  $order->save($notify);
        if($orderresult){
            Db::commit();
        }else{
            Db::rollback();
            return false;
        }
        $order = $order->where('out_trade_no', $order->out_trade_no)->find();
        if($order->status!="used"){
            return false;
        }

        if (empty($order->box_id)) {
            $this->error('请选择盲盒');
        }
//        if (!in_array($order->num, [1, 5, 9])) {
//            $this->error('开箱数量有误');
//        }

        // 检查盲盒是否可以试玩
        // $box = BoxModel::where('id', $order->box_id)->value('id');
        $box = BoxModel::where('id', $order->box_id)->field('id,detail_id')->find();
        if (empty($box['id'])) {
            $this->error('盲盒有误');
        }
        //用户拥有指定藏品的数量
        $num = CollectModel::where("goods_id",$box['detail_id'])->where("user_id",$this->auth->id)->count();
        try {
            // 抽奖 begin
//                $goodsIds = [Detail::getOne($order->box_id,$num)];
            $result = Detail::getOne($order->box_id,$num,$this->auth->id);
           
        } catch (\Exception $e) {

            $this->error('抽奖失败');
        }
        // 抽奖 end

        if($result['status']==2){
            $this->error($result['txt']);
        }
        
        $goods = Goods::alias('a')
                ->field('a.*,b.name as cate_name,b.color,a.sales')
                ->join('shopro_category b','b.id=a.category_ids')
                ->where('a.id', $result['data'])->find();
        
        // 抽奖失败
        if (empty($goods)) {

            $this->error('抽奖失败');
        }

        $prizeInfo = [];
        Db::startTrans();
        try {

            // 创建开箱记录
            $prize = Prizerecord::create([
                'box_id' => $order->box_id,
                'order_id' => $order->id,
                'out_trade_no' => $order->out_trade_no,
                'user_id' =>  $this->auth->id?$this->auth->id:$order->user_id,
                'goods_id' => $goods->id,
                'goods_name' => $goods->title,
                'goods_image' => $goods->image,
                'goods_coin_price' => $goods->price,
                'goods_rmb_price' => round($goods->price, 2),
                'status' => 'bag', // 奖品状态:bag=盒柜,exchange=已回收,delivery=申请发货,received=已收货
            ]);

            // 减少商品库存
            GoodsSkuPrice::where('goods_id', $goods->id)->setDec('stock');
            
            $sku = GoodsSkuPrice::where('goods_id', $goods->id)->field('sales')->find();
            //写入文昌链 上链

            //购买 todo:上链
            $data = [
                'user_id'=>$this->auth->id?$this->auth->id:$order->user_id,
                'original_price' => $order->rmb_amount,
                'goods_id'=>$goods->id,
                'type'=>4,
                'status'=>0,
                'sn' => $sku['sales'] + 1,
            ];
            $res =\addons\shopro\model\UserCollect::edit($data);
            GoodsSkuPrice::where('goods_id', $goods->id)->setInc('sales');

            $prizeInfo[] = [
                'prize_id' => intval($prize->id),
                'image' => $prize->goods_image ? cdnurl($prize->goods_image, true) : '',
                'goods_name' => $prize->goods_name,
                'cate_name' =>$goods->cate_name,
                'color' =>$goods->color,
            ];
            

            // 增加盲盒销量
            BoxModel::where('id', $order->box_id)->setInc('sales', $order->num);

        } catch (\Exception $e) {

            Db::rollback();
            // 退款
            $this->error($e->getMessage());
            $this->error('抽奖失败');
        }
        // 订单状态改为已使用
        $order->save(['status' => 'used', 'backend_read' => 0]);

        Db::commit();

        $ret = [
            'select' => $order->select,
            'prizeInfo' => $prizeInfo
        ];

        return $ret;
    }





















}