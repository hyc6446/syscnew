<?php


namespace addons\shopro\controller;


use addons\shopro\library\PayService;
use think\Exception;
use think\Log;

class Api extends Base
{
    protected $noNeedLogin = ['*'];
    protected $noNeedRight = ['*'];

    /**
     * 同步余额
     */
    public function getRechargeData()
    {
        $rechargeModel = new \addons\shopro\model\TradeOrder();

        $order = $rechargeModel->where('status','<',1)->field('id,order_sn,user_id,status')->order('id desc')->limit(0,100)->select();


        $pay = new PayService('alipay','H5');

        foreach ($order as  $item){
            try {
                $result =  $pay->find($item['order_sn']);
                Log::write('Alipay-result: '. $result);
            }catch (Exception $e){
                continue;
            }
//           $data = json_decode($result,true);
        }
    }


    /**
     * 同步上下级关系(排行榜数量)
     */
    public function getRankData()
    {

    }







}