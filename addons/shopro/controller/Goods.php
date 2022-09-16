<?php

namespace addons\shopro\controller;

use addons\shopro\exception\Exception;
use addons\shopro\job\OrderAutoOper;
use addons\shopro\job\OrderPayed;
use app\common\library\Sms;
use think\Log;

class Goods extends Base
{

    protected $noNeedLogin = ['index', 'detail', 'lists', 'activity', 'seckillList', 'grouponList', 'store', 'calendar', 'composeList', 'close'];
    protected $noNeedRight = ['*'];

    public function index()
    {
        // 测试腾讯云短信
        $user = \addons\shopro\model\User::get(10011);
        $walletLog = \addons\shopro\model\UserWalletLog::get(61);

        $user && $user->notify(
            new \addons\shopro\notifications\Wallet([
                'walletLog' => $walletLog,
                'event' => 'wallet_change'
            ])
        );
    }

    public function detail()
    {
        $id = $this->request->get('id');
        $detail = \addons\shopro\model\Goods::getGoodsDetail($id);

        // 记录足记
        \addons\shopro\model\UserView::addView($detail);
        $this->success('商品详情', $detail);
    }

    public function lists()
    {
        $params = $this->request->get();
        $params['islist'] = $params['islist'] ?? 1;
        $params['tag'] = $params['tag'] ?? '';
        $data = \addons\shopro\model\Goods::getGoodsList($params);
        $this->success('商品列表', $data);
    }

    public function calendar()
    {
        $params = $this->request->get();
        $data = \addons\shopro\model\Goods::getGoodsList($params, false, true);

        $this->success('商品列表', $data);
    }


    /**
     * 获取商品支持的 自提点
     */
    public function store()
    {
        $params = $this->request->get();
        $data = \addons\shopro\model\Goods::getGoodsStore($params);

        $this->success('自提列表', $data);
    }


    // 秒杀列表
    public function seckillList()
    {
        $params = $this->request->get();

        $this->success('秒杀商品列表', \addons\shopro\model\Goods::getSeckillGoodsList($params));
    }


    // 拼团列表
    public function grouponList()
    {
        $params = $this->request->get();

        $this->success('拼团商品列表', \addons\shopro\model\Goods::getGrouponGoodsList($params));
    }


    public function activity()
    {
        $activity_id = $this->request->get('activity_id');
        $activity = \addons\shopro\model\Activity::get($activity_id);
        if (!$activity) {
            $this->error('活动不存在', null, -1);
        }

        $goods = \addons\shopro\model\Goods::getGoodsList(['goods_ids' => $activity->goods_ids]);
        $activity->goods = $goods;

        $this->success('活动列表', $activity);
    }

    public function favorite()
    {
        $params = $this->request->post();
        $result = \addons\shopro\model\UserFavorite::edit($params);
        $this->success($result ? '收藏成功' : '取消收藏', $result);
    }

    public function favoriteList()
    {
        $page = $this->request->get('page', 1);
        $limit = $this->request->get('limit', 10);
        $data = \addons\shopro\model\UserFavorite::getGoodsList($page, $limit);
        $this->success('收藏列表', $data);
    }


    public function viewDelete()
    {
        $params = $this->request->post();
        $result = \addons\shopro\model\UserView::del($params);
        $this->success('删除成功', $result);
    }


    public function viewList()
    {
        $data = \addons\shopro\model\UserView::getGoodsList();
        $this->success('商品浏览列表', $data);
    }


    //订阅提醒
    public function ding()
    {
        $params = $this->request->post();
        $result = \addons\shopro\model\GoodsDing::ding($params);
        $this->success($result ? '订阅成功' : '取消订阅');
    }

    //合成列表
    public function composeList()
    {
        $params = $this->request->get();
        $data = (new \addons\shopro\model\Goods())->composeList($params, $this->auth->id ?: 0);
        // var_dump($data);exit;
        $this->success('合成藏品列表', $data);
    }

    //藏品合成
    public function compose()
    {
        $goodsId = $this->request->post('id', 0);
        $result = (new \addons\shopro\model\Goods())->compose($goodsId, $this->auth->id);
        if (isset($result['msg']) && $result['msg']) {
            $this->error($result['msg'], $result);
        } else {
            $this->success('合成成功', $result);
        }
    }


    public function close()
    {
        $res = Sms::notice('18302887308', '111111', 'SMS_222140179');
        \think\Queue::later((0), '\addons\shopro\job\GoodsDing@ding', ['id' => 2], 'shopro');
        $this->success('合成成功', $res);
    }
}
