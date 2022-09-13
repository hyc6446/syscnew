<?php

namespace addons\shopro\controller;

use addons\shopro\model\Chest as ChestModel;
use addons\shopro\model\ChestOrder as ChestOrderModel;
use addons\shopro\model\Goods as GoodsModel;
use addons\shopro\model\UserCollect as CollectModel;
use addons\shopro\model\GoodsSkuPrice as GoodsSkuPrice;
use addons\shopro\model\Detail as BoxDetail;
use addons\shopro\model\BoxOrder as BoxOrder;
use addons\shopro\model\User as UserList;
use think\Db;
use think\Exception;

class Chest extends Base
{

    protected $noNeedLogin = ['chestList',"taigerNums","sendBoxNum","taigerNumAdd","canelData",'allNum','allUserNum',"allTeigerNum","allStatusUserNum","allStatusTeigerNum"];
    protected $noNeedRight = ['*'];
    
    //赠送用户盲盒次数
    public function sendBoxNum()
    {
        // $p_user = UserList::where(['share_count'=>1])->file("id,share_count,box_num")->select();
        $goodsIds = UserList::where(['share_count'=>array(">",3)])->field("id,share_count,box_num")->select();
        // var_dump($goodsIds);exit;
        foreach ($goodsIds as $key => $value){
            if($value['share_count']>3&&$value['share_count']<31){
                $box_num = $value['box_num'] + 2;
                $data['box_num'] = $box_num;
                \addons\shopro\model\User::update($data,["id"=>$value['id']]);
            }else if($value['share_count']>30){
                $box_num = $value['box_num'] + 4;
                $data['box_num'] = $box_num;
                \addons\shopro\model\User::update($data,["id"=>$value['id']]);
            }
        }
        $this->success('宝箱列表', 1);
    }
    
    //去除异常数据
    public function canelData()
    {
        $goodsIds = BoxOrder::where(['box_id'=>30,"status"=>"used"])->group("user_id")->field("id,user_id")->select();
        $user_id = array();
        $coll_id = array();
        foreach ($goodsIds as $key => $value){
            $ordernnum = BoxOrder::where(["user_id"=>$value['user_id'],"box_id"=>30,"status"=>"used"])->count();
            $collnum = CollectModel::where(['goods_id'=>array("in",[31,32,33]),"user_id"=>$value['user_id']])->count();
            if($ordernnum<$collnum){
                $teigernum = CollectModel::where(['goods_id'=>31,"user_id"=>$value['user_id']])->count();
                if($teigernum>1){
                    $coll = CollectModel::where(['goods_id'=>31,"user_id"=>$value['user_id']])->field("id")->find();
                    $coll_id[] = $coll['id'];
                    $user_id[] = $value['user_id'];
                }
            }
        }
        //删除老虎
        CollectModel::where(['goods_id'=>31,"user_id"=>array("in",$user_id),"id"=>array("not in",$coll_id)])->delete();
        //删除勋章和创世
        CollectModel::where(["user_id"=>array("in",$user_id),"goods_id"=>array("in",[34,35])])->delete();
        //删除开箱记录
        ChestOrderModel::where(["user_id"=>array("in",$user_id)])->delete();
        $this->success('宝箱列表', 1);
    }
    
    //统计老虎数量增量
    public function taigerNumAdd()
    {
        $goodsIds = CollectModel::where(['goods_id'=>31])->group("user_id")->field("user_id")->select();
        $two_num = 0;
        $three_num = 0;
        foreach ($goodsIds as $key => $value){
            $collnum = CollectModel::where(['goods_id'=>31,"user_id"=>$value['user_id']])->count();
            if($collnum==2){
                $two_num++;
            }
            if($collnum>=3){
                $three_num++;
            }
        }
        $result['two_num'] = $two_num - 12;
        $result['three_num'] = $three_num - 5;
        $this->success('宝箱列表', $result);
    }
    
    //统计老虎数量
    public function taigerNums()
    {
        $goodsIds = CollectModel::where(['goods_id'=>31])->group("user_id")->field("user_id")->select();
        $two_num = 0;
        $three_num = 0;
        foreach ($goodsIds as $key => $value){
            $collnum = CollectModel::where(['goods_id'=>31,"user_id"=>$value['user_id']])->count();
            if($collnum==2){
                $two_num++;
            }
            if($collnum>=3){
                $three_num++;
            }
        }
        $result['two_num'] = $two_num;
        $result['three_num'] = $three_num;
        $this->success('宝箱列表', $result);
    }

    
    //异常用户数量统计
    public function allUserNum()
    {
        $goodsIds = BoxOrder::where(['box_id'=>30,"status"=>"used"])->group("user_id")->field("id,user_id")->select();
        $num = 0;
        foreach ($goodsIds as $key => $value){
            $ordernnum = BoxOrder::where(["user_id"=>$value['user_id'],"box_id"=>30,"status"=>"used"])->count();
            $collnum = CollectModel::where(['goods_id'=>array("in",[31,32,33]),"user_id"=>$value['user_id']])->count();
            if($ordernnum<$collnum){
                $num++;
            }
        }
        $this->success('宝箱列表', $num);
    }
    
    //正常用户数量统计
    public function allStatusUserNum()
    {
        $goodsIds = BoxOrder::where(['box_id'=>30,"status"=>"used"])->group("user_id")->field("id,user_id")->select();
        $num = 0;
        foreach ($goodsIds as $key => $value){
            $ordernnum = BoxOrder::where(["user_id"=>$value['user_id'],"box_id"=>30,"status"=>"used"])->count();
            $collnum = CollectModel::where(['goods_id'=>array("in",[31,32,33]),"user_id"=>$value['user_id']])->count();
            if($ordernnum==$collnum){
                $num++;
            }
        }
        $this->success('宝箱列表', $num);
    }
    
    //异常用户下老虎大于1的用户数量
    public function allTeigerNum()
    {
        $goodsIds = BoxOrder::where(['box_id'=>30,"status"=>"used"])->group("user_id")->field("id,user_id")->select();
        $two_num = 0;
        $three_num = 0;
        foreach ($goodsIds as $key => $value){
            $ordernnum = BoxOrder::where(["user_id"=>$value['user_id'],"box_id"=>30,"status"=>"used"])->count();
            $collnum = CollectModel::where(['goods_id'=>array("in",[31,32,33]),"user_id"=>$value['user_id']])->count();
            if($ordernnum>$collnum){
                $teigernum = CollectModel::where(['goods_id'=>31,"user_id"=>$value['user_id']])->count();
                if($teigernum==2){
                    $two_num++;
                }
                if($teigernum>=3){
                    $three_num++;
                }
            }
        }
        $result['two_num'] = $two_num;
        $result['three_num'] = $three_num;
        $this->success('宝箱列表', $result);
    }
    
    //正常用户下老虎大于1的用户数量
    public function allStatusTeigerNum()
    {
        $goodsIds = BoxOrder::where(['box_id'=>30,"status"=>"used"])->group("user_id")->field("id,user_id")->select();
        $two_num = 0;
        $three_num = 0;
        foreach ($goodsIds as $key => $value){
            $ordernnum = BoxOrder::where(["user_id"=>$value['user_id'],"box_id"=>30,"status"=>"used"])->count();
            $collnum = CollectModel::where(['goods_id'=>array("in",[31,32,33]),"user_id"=>$value['user_id']])->count();
            if($ordernnum==$collnum){
                $teigernum = CollectModel::where(['goods_id'=>31,"user_id"=>$value['user_id']])->count();
                if($teigernum==2){
                    $two_num++;
                }
                if($teigernum>=3){
                    $three_num++;
                }
            }
        }
        $result['two_num'] = $two_num;
        $result['three_num'] = $three_num;
        $this->success('宝箱列表', $result);
    }
    
    //查询宝箱
    public function chestList()
    {
        $uid = $this->auth->id;
        $chest = ChestModel::field("id as chest_id,name,goods_id,start_num,desc,pic,detail_id,items_name")->select();
        // var_dump($uid);exit;
        foreach ($chest as $key => $value){
            //查询指定商品
            $goods = GoodsModel::where(['id'=>$value['goods_id']])->field("title")->find();
            //查询藏品
            $detail = GoodsModel::where(['id'=>$value['detail_id']])->field("title")->find();
            //查询用户完成数量
            if($uid){
                $chest[$key]['num'] = CollectModel::where(["user_id"=>$uid,"goods_id"=>$value['goods_id'],"status"=>0,"is_status"=>1])->count();
            }else{
                $chest[$key]['num'] = 0;
            }
            $chest[$key]['goods_title'] = $goods['title'];
            $chest[$key]['detail_title'] = $detail['title'];
        }
        $this->success('宝箱列表', $chest);
    }
    
    //查询数量
    public function chestNum()
    {
        $uid = $this->auth->id;
        $goldsChest = ChestModel::where(['id'=>1])->field("items_name")->find();
        $sliverChest = ChestModel::where(['id'=>2])->field("items_name")->find();
        $chest['goldsChest'] = $goldsChest['items_name'];
        $chest['sliverChest'] = $sliverChest['items_name'];
        $chest['goldsNum'] = ChestOrderModel::where(['user_id'=>$uid,"chest_id"=>1,"product"=>2])->count();
        $chest['sliverNum'] = ChestOrderModel::where(['user_id'=>$uid,"chest_id"=>2,"product"=>2])->count();
        $this->success('查询活动奖品数量', $chest);
    }


    //打开宝箱
    public function openChest()
    {
        $chest_id = input('chest_id/d');
        $product = input('product/d');
        $contact = input('contact', '');
        $mobile = input('mobile', '');
        $address = input('address', '');
        $uid = $this->auth->id;
        $order = ChestOrderModel::where(['user_id'=>$uid,"chest_id"=>$chest_id])->field("id")->find();
        $chest = ChestModel::where(['id'=>$chest_id])->field("start_num,goods_id,detail_id")->find();
        //查询用户完成数量
        $num = CollectModel::where(["user_id"=>$uid,"goods_id"=>$chest['goods_id'],"status"=>0,"is_status"=>1])->count();
        if($num<$chest['start_num']){
            $this->error('您未达成开箱条件');
        }
        if($chest_id==1){
            $chest_num = $this->auth->gold_chest_num;
            $chestData['gold_chest_num'] = $chest_num - 1;
        }else{
            $chest_num = $this->auth->sliver_chest_num;
            $chestData['sliver_chest_num'] = $chest_num - 1;
        }
        if($chest_num<1){
            $this->error('开箱次数已用完');
        }

        Db::startTrans();
        try {
            //创建开宝箱订单
            $data['user_id'] = $uid;
            $data['chest_id'] = $chest_id;
            $data['product'] = $product;
            $data['add_time'] = time();
            $data['address'] = $address;
            $data['contact'] = $contact;
            $data['mobile'] = $mobile;
            $ret = ChestOrderModel::create($data);
            //如果是藏品就空投
            if($product==1){
                $this->grantShard($chest['detail_id'],$uid);
            }
            //扣除开箱次数
            \addons\shopro\model\User::update($chestData,["id"=>$this->auth->id]);
        } catch (Exception $e) {
            Db::rollback();
            $this->error('打开宝箱失败');
        }
        Db::commit();
        $this->success('打开成功', $ret);
    }

    //空投藏品
    public function grantShard($ids,$uid)
    {
        $user = \app\admin\model\User::get($uid);
        if (!$user)$this->error('用户不存在,请确认后再输入');
        if (!$user['addr'])$this->error('该用户未登录小程序注册数字资产账户');

        //库存判断
        $sku = \app\admin\model\shopro\goods\SkuPrice::where('goods_id', $ids)->field('stock')->find();
        if($sku['stock']<1){
            $this->error('该藏品没有库存');
        }
        //空投 todo:上链
        // var_dump($user['id']);exit;
        $res =\addons\shopro\model\UserCollect::edit([
            'user_id'=>$user['id'],
            'goods_id'=>$ids,
            'type'=>5,
            'status'=>0,
        ]);
        // var_dump($res);exit;
        if (!$res){
            $this->error('空投失败');
        }
        $goodsSkuPrice = \addons\shopro\model\GoodsSkuPrice::where('goods_id',$ids)->find();
        if ($goodsSkuPrice) {
            $goodsSkuPrice->setDec('stock', 1);         // 减少库存
            $goodsSkuPrice->setInc('sales', 1);         // 增加销量
        }
        return 1;
    }

}
