<?php


namespace addons\shopro\controller;


use addons\shopro\controller\commission\Log;
use addons\shopro\exception\Exception;
use fast\Auth;

class UserCollect extends Base
{
    protected $noNeedLogin = ['*'];
    protected $noNeedRight = ['*'];


    public function lists()
    {
        $params = $this->request->get();
        $uid = $this->auth->id;
        $list = \addons\shopro\model\UserCollect::getList($params,$uid);
        $this->success('我的艺术品',$list);
    }

    public function detail()
    {
        $id = $this->request->get('id',0);
        $uid = $this->auth->id;
        $collect = (new \addons\shopro\model\UserCollect())->getOne($id,$uid);
        $data = \addons\shopro\model\Goods::getGoodsDetail($collect['goods_id'],true);
        $collect['nike_name'] = $this->auth->getUserinfo()['nickname'];
        $data['collect_info'] = $collect;
        $this->success('我的艺术品详情',$data);
    }

    //寄售
    public function conSales()
    {
        $params = $this->request->post('collect','');
        $uid = $this->auth->id;
        if (!$params)$this->error('请选择寄售的藏品');
        $collect = json_decode(str_replace('&quot;','"',$params),true);
        foreach ($collect as $value){
            $collect = (new \addons\shopro\model\UserCollect())->getOne($value['id'],$uid);
            if ($collect['status']==1)$this->error('所选藏品正在寄售');
            if ($collect['price']<0)$this->error('寄售藏品的价格必须大于零');
            $res =\addons\shopro\model\UserCollect::edit(['id'=>$value['id'],'user_id'=>$uid,'price'=>$value['price'],'status'=>1]);
        }
        $this->success('发起寄售成功');

    }

    public function give()
    {
        $ids = $this->request->post('ids','');
        $mobile = $this->request->post('mobile','');
        $uid = $this->auth->id;
        if (!$ids)$this->error('请选择转赠的藏品');
        if (!$mobile)$this->error('请选择转赠的好友');
        $user = \addons\shopro\model\User::get(['mobile'=>$mobile]);
        if (!$user)$this->error('转赠的好友不存在,请确认后再输入');
        if ($user['id']== $uid)$this->error('转赠的好友不能为自己');
        if (!$user['addr'])$this->error('好友未重新登录注册数字资产账户');
        $collect = explode(',',$ids);
        foreach ($collect as $value){
            $collect = (new \addons\shopro\model\UserCollect())->getOne($value,$uid);
            //链上转移资产
            $res = \addons\shopro\model\UserCollect::transferShard($collect['goods_id'],$collect['user_id'],$user['id'],$collect['asset_id'],$collect['shard_id']);
            \think\Log::info('链上转移资产:::::'.json_encode($res));
            if (!isset($res['response']['errno']) || $res['response']['errno']!= 0){
                $this->error('链上转移资产失败');
            }
            //转赠 todo:上链
            $res =\addons\shopro\model\UserCollect::edit([
                'user_id'=>$user['id'],
                'goods_id'=>$collect['goods_id'],
                'give_user_id'=>$uid,
                'type'=>3,
                'status'=>0,
                'notShard'=>1,
                'asset_id'=>$collect['asset_id'],
                'shard_id'=>$collect['shard_id'],
            ]);
            if (!$res){
                $this->error('转赠失败');
            }
            //销毁
            $res =\addons\shopro\model\UserCollect::edit(['id'=>$value,'user_id'=>$uid,'status'=>4,'status_time'=>time(),'is_consume'=>1]);
            if (!$res){
                $this->error('转赠失败');
            }
        }
        $this->success('转赠成功');

    }

    /**
     * 售卖大厅
     */
    public function hall()
    {
        $params = $this->request->get();
        $uid = $this->auth->id;
        $list = \addons\shopro\model\UserCollect::getList($params,$uid,'hall');
        $this->success('寄售大厅',$list);
    }

    public function hallDetail()
    {
        $id = $this->request->get('id',0);
        $collect = (new \addons\shopro\model\UserCollect())->getOne($id,0);
        $data = \addons\shopro\model\Goods::getGoodsDetail($collect['goods_id'],true);
        if ($data){
            $data['original_price'] = $data['price'];
            $data['price'] = $collect['price'];
        }
        $data['collect_info'] = $collect->visible(['asset_id','shard_id','give_user_id','owner_addr','querysds','token','card_id','trans_hash','card_time','add']);
        $this->success('寄售大厅藏品详情',$data);
    }



    public function createCollectOrder()
    {
        $post = $this->request->post();
        if (!$post['id'])$this->error('请选择购买的藏品');
        $collect = (new \addons\shopro\model\UserCollect())->getOne($post['id'],0);
        if ($collect['status']!=1)$this->error('该藏品暂未出售');

        $params['goods_list'] = [];
        $params['goods_list'][0]['goods_id'] = $collect['goods_id'];
        $params['goods_list'][0]['goods_num'] = 1;
        $params['goods_list'][0]['sku_price_id'] = 0;
        $params['goods_list'][0]['goods_price'] = $post['price'];
        $params['goods_list'][0]['dispatch_type'] = 'autosend';
        $params['goods_list'][0]['user_collect_id'] =$post['id'];
        $params['address_id'] = '';
        $params['buy_type'] = 'alone';
        $params['coupons_id'] = 0;
        $params['from'] = 'goods';


        $order = \addons\shopro\model\Order::createOrder($params);

        $this->success('订单添加成功', $order);
    }
}