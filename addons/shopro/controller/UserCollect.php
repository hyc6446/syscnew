<?php


namespace addons\shopro\controller;


// use addons\shopro\controller\commission\Log;
use addons\shopro\exception\Exception;
use addons\shopro\model\Config as configModel;
use fast\Auth;
use think\Log;

class UserCollect extends Base
{
    protected $noNeedLogin = ['checkSendSwith', 'hallList'];
    protected $noNeedRight = ['*'];


    public function lists()
    {
        $params = $this->request->get();
        $uid = $this->auth->id;
        $list = \addons\shopro\model\UserCollect::getList($params, $uid);
        $this->success('我的艺术品', $list);
    }
    // 盯链
    public function hallList()
    {
        $list = \addons\shopro\model\UserCollect::getHallList();
        $this->success('执行成功', $list);
    }


    public function detail()
    {
        $id = $this->request->get('id', 0);
        $uid = $this->auth->id;
        $collect = (new \addons\shopro\model\UserCollect())->getOne($id, $uid);
        $data = \addons\shopro\model\Goods::getGoodsDetail($collect['goods_id'], true);
        $collect['nike_name'] = $this->auth->getUserinfo()['nickname'];

        $data['collect_info'] = $collect;
        $this->success('我的艺术品详情', $data);
    }

    //查询转赠开关
    public function checkSendSwith()
    {
        $config = configModel::where('name', 'shopro')->field('value')->find();
        $data = json_decode($config['value'], true);
        $this->success('转赠开关', $data['send_status']);
    }

    //寄售
    public function conSales()
    {
        $params = $this->request->post('collect', '');
        $uid = $this->auth->id;
        if (!$this->auth->wcl_status) $this->error('您的账户正在上链中,暂时无法交易');
        if (!$params) $this->error('请选择寄售的藏品');
        $collect = json_decode(str_replace('&quot;', '"', $params), true);
        foreach ($collect as $value) {
            $collect = (new \addons\shopro\model\UserCollect())->getOne($value['id'], $uid);
            if ($collect['status'] == 1) $this->error('所选藏品正在寄售');
            if (!$collect['nft_id']) $this->error('所选藏品正在上链中,请待会再试');
            if ($collect['wcl_status'] != 1) $this->error('所选藏品正在上链中,请待会再试');
            if ($value['sellPrice'] <= 0) $this->error('寄售藏品的价格必须大于零');
            // $res =\addons\shopro\model\UserCollect::edit(['id'=>$value['id'],'user_id'=>$uid,'price'=>$value['sellPrice'],'status'=>1]);
            $res = \addons\shopro\model\UserCollect::update(['price' => $value['sellPrice'], 'status' => 1], ['id' => $value['id'], 'user_id' => $uid]);
        }
        $this->success('发起寄售成功');
    }

    //下架寄售藏品
    public function underCollect()
    {
        $collect_id = $this->request->post('collect_id', '');
        $uid = $this->auth->id;
        if (!$collect_id) $this->error('请选择寄售的藏品');
        $collect = (new \addons\shopro\model\UserCollect())->getOne($collect_id, $uid);
        if (!$collect['status'] == 1) $this->error('藏品状态不正确，请刷新');
        $res = \addons\shopro\model\UserCollect::update(['status' => 0], ['id' => $collect_id, 'user_id' => $uid]);
        if ($res) {
            $this->success('下架成功', 1);
        } else {
            $this->error('下架失败');
        }
    }

    public function give()
    {
        $ids = $this->request->post('ids', '');
        $mobile = $this->request->post('mobile', '');
        $this->error('暂时无法转赠');
        $uid = $this->auth->id;
        if (!$this->auth->wcl_status) $this->error('您的账户正在上链中,暂时无法交易');
        if (!$ids) $this->error('请选择转赠的藏品');
        if (!$mobile) $this->error('请选择转赠的好友');
        $user = \addons\shopro\model\User::get(['mobile' => $mobile]);
        if (!$user) $this->error('转赠的好友不存在,请确认后再输入');
        if ($user['id'] == $uid) $this->error('转赠的好友不能为自己');
        if (!$user['addr']) $this->error('好友未重新登录注册数字资产账户');
        $collects = explode(',', $ids);
        // foreach ($collects as $value){
        //     $collect = (new \addons\shopro\model\UserCollect())->getOne($value,$uid);
        //     //限制转赠
        //     // if($collects['goods_id']==31||$collects['goods_id']==36){
        //     //     $this->error('引力数藏首发·虎藏品和创世不支持转赠');
        //     // }
        //     if($collect['is_hook']==1){
        //         $this->error('冷却时间30天');
        //     }
        // }
        foreach ($collects as $value) {
            $collect = (new \addons\shopro\model\UserCollect())->getOne($value, $uid);

            if (!$collect['nft_id']) $this->error('所选藏品正在上链中,请待会再试');

            //链上转移资产
            $result = \addons\shopro\model\UserCollect::transferShard($collect['asset_id'], $uid, $user['id'], $collect['nft_id']);
            \think\Log::info('链上转移资产:::::' . json_encode($result));

            //转赠 todo:上链
            $res = \addons\shopro\model\UserCollect::edit([
                'user_id' => $user['id'],
                'goods_id' => $collect['goods_id'],
                'give_user_id' => $uid,
                'give_collect_id' => $value,
                'type' => 3,
                'status' => 0,
                'notShard' => 1,
                'asset_id' => $collect['asset_id'],
                'sn' => $collect['sn'],
                'operation_id' => $result['data']['operation_id'] ?? '',
                'task_id' => $result['data']['task_id'] ?? '',
            ]);
            if (!$res) {
                $this->error('转赠失败');
            }
            // var_dump($collect['sn']);
            //销毁
            $res = \addons\shopro\model\UserCollect::edit(['id' => $value, 'user_id' => $uid, 'status' => 4, 'status_time' => time(), 'is_consume' => 1, 'to_user_id' => $user['id'], 'sn' => $collect['sn']]);
            if (!$res) {
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
        $list = \addons\shopro\model\UserCollect::getList($params, $uid, 'hall');
        $this->success('寄售大厅', $list);
    }

    public function hallDetail()
    {
        $id = $this->request->get('id', 0);
        $collect = (new \addons\shopro\model\UserCollect())->getOne($id, 0);
        $data = \addons\shopro\model\Goods::getGoodsDetail($collect['goods_id'], true);
        if ($data) {
            $data['original_price'] = $data['price'];
            $data['price'] = $collect['price'];
        }
        $data['user_collect_id'] = $id;
        $data['collect_info'] = $collect->visible(['asset_id', 'shard_id', 'give_user_id', 'owner_addr', 'sn', 'querysds', 'token', 'card_id', 'trans_hash', 'card_time', 'add']);
        $this->success('寄售大厅藏品详情', $data);
    }



    public function createCollectOrder()
    {
        $post = $this->request->post();
        if (!$post['id']) $this->error('请选择购买的藏品');
        $collect = (new \addons\shopro\model\UserCollect())->getOne($post['id'], 0);
        if ($collect['status'] != 1) $this->error('该藏品暂未出售');
        if ($collect['wcl_status'] != 1) $this->error('该藏品暂未上链');
        $params['goods_list'] = [];
        $params['goods_list'][0]['goods_id'] = $collect['goods_id'];
        $params['goods_list'][0]['goods_num'] = 1;
        $params['goods_list'][0]['sku_price_id'] = 0;
        $params['goods_list'][0]['goods_price'] = $post['price'];
        $params['goods_list'][0]['dispatch_type'] = 'autosend';
        $params['goods_list'][0]['dispatch_id'] = 1;
        $params['goods_list'][0]['user_collect_id'] = $post['id'];
        $params['goods_list'][0]['activity_type'] = $post['activity'];
        $params['address_id'] = '';
        $params['buy_type'] = 'alone';
        $params['coupons_id'] = 0;
        $params['from'] = 'goods';


        $order = \addons\shopro\model\Order::createOrder($params);

        $this->success('订单添加成功', $order);
    }


    public function giveLog()
    {
        $params = $this->request->get();
        $uid = $this->auth->id;
        $list = \addons\shopro\model\UserCollect::getGiveList($params, $uid);
        $this->success('转赠记录', $list);
    }
}
