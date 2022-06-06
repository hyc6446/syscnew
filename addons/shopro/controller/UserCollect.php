<?php


namespace addons\shopro\controller;


use addons\shopro\exception\Exception;

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
        $data = \addons\shopro\model\Goods::getGoodsDetail($id,true);
        $res['collect_info'] = $collect;
        $res['goods_info'] = $data;
        $this->success('我的艺术品详情',$res);
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
        $params = $this->request->post('collect','');
        $code = $this->request->post('referral_code','');
        $uid = $this->auth->id;
        if (!$params)$this->error('请选择转赠的藏品');
        if (!$code)$this->error('请选择转赠的好友');
        $user = \addons\shopro\model\User::get(['referral_code'=>$code]);
        if (!$user)$this->error('转赠的好友不存在,请确认后再输入');
        if ($user['id']== $uid)$this->error('转赠的好友不能为自己');
        $collect = explode(',',$params);
        foreach ($collect as $value){
            $collect = (new \addons\shopro\model\UserCollect())->getOne($value,$uid);
            //转赠 todo:上链
            $res =\addons\shopro\model\UserCollect::edit([
                'user_id'=>$user['id'],
                'goods_id'=>$collect['goods_id'],
                'give_user_id'=>$uid,
                'type'=>3,
                'status'=>0,
            ]);
            if (!$res){
                new Exception('转赠失败');
            }
            //销毁
            $res =\addons\shopro\model\UserCollect::edit(['id'=>$value,'user_id'=>$uid,'status'=>4,'status_time'=>time(),'is_consume'=>1]);
            if (!$res){
                new Exception('转赠失败');
            }
        }
        $this->success('转赠成功');

    }
}