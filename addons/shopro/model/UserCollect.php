<?php

namespace addons\shopro\model;

use addons\shopro\exception\Exception;
use addons\xasset\library\Service;
use app\admin\model\Admin;
use think\Db;
use think\Log;
use think\Model;

/**
 * 用户藏品
 * Class UserCollect
 * @package addons\shopro\model
 */
class UserCollect extends Model
{

    // 表名
    protected $name = 'shopro_user_collect';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'status_time';
    
    // 追加属性
    protected $append = [
        'image','status_text','type_text','status_time_text'
    ];

    protected $hidden =  ['asset_id','shard_id','give_user_id','owner_addr','querysds']
    ;
    public function getImageAttr($value, $data)
    {
        if (!empty($value)) return cdnurl($value, true);

    }

    public function status()
    {
        return ['正常','正在寄售','已售出','已合成','已赠予'];
    }

    public function getStatusTextAttr($value, $data)
    {
        //0=正常,1=正在寄售,2=已售出,3=已合成,4=已赠予
        return $this->status()[$data['status']];
    }
    public function getTypeTextAttr($value, $data)
    {
        return ['1'=>'购买','2'=>'合成','3'=>'赠送','4'=>'盲盒','5'=>'空投'][$data['from_type']];
    }

    public function getStatusTimeTextAttr($value,$data)
    {
        return $data['status_time']?date('Y-m-d H:i',$data['status_time']):'';
    }

    public function goods()
    {
        return $this->hasOne(Goods::class);
    }

    public static function edit($params)
    {

        extract($params);
        try {
            Db::startTrans();
            if (isset($id)&&$id){
                $collect = self::where(['user_id' => $user_id, 'id' => $id])->find();
                if ($collect) {
                    $collect->is_consume = $is_consume??0;//链上 资产是否销毁
                    $collect->status = $status??0;//状态:0=正常,1=正在寄售,2=已售出,3=已合成,4=已赠予
                    if (isset($price))$collect->price = $price??0;//寄售价格
                    $collect->save();
                }else{
                    Db::rollback();
                    new Exception('藏品不存在');
                }
            } else {
                $user = User::get($user_id);
                $goods = Goods::withTrashed()->where('id',$goods_id)->find();
                $brand = GoodsBrand::where( 'id','in',explode(',',$goods['brand_ids']))->column('name');
                $collect = new self();
                $service = new Service();
                //上链
                $admin = Db::name('admin')->where('id',$goods['admin_id'])->find();
                if (!isset($notShard) && $admin && $goods['asset_id'] && $user['addr'] ){
                    $asset_id = $goods['asset_id'];
                    $shard_id = gen_asset_id($service->appId);
                    $userId = $user_id;
                    $account = array(
                        'address' => $admin['addr'],
                        'public_key' => $admin['public_key'],
                        'private_key' => $admin['private_key'],
                    );
                    $price = ($original_price??0)*100;
                    $res = $service->grantShard($account, $goods['asset_id'], $shard_id, $user['addr'], $price, $userId);
                    Log::info('授予资产碎片:::::'.json_encode($res));
                }
                $collect->user_id = $user_id;//藏品所有者
                $collect->goods_id = $goods_id;//藏品id
                $collect->original_price = $original_price??0;//藏品原价格
                $collect->asset_id = $asset_id??0;//链上 资产id
                $collect->shard_id = $shard_id??0;//链上 碎片id
                $collect->give_user_id = $give_user_id??0;//赠予人
                $collect->is_consume = 0;//链上 资产是否销毁
                $collect->owner_addr = $user['addr']??'';//资产账户地址
                $collect->querysds = $querysds??'';//资产信息json
                $collect->status = 0;//状态:0=正常,1=正在寄售,2=已售出,3=已合成,4=已赠予
                $collect->from_type = $type;
                $collect->token = md5($user_id.'token-'.$user['referral_code'].time());
                $collect->up_brand = $brand?implode('&',$brand):'-';
                $collect->auth_brand = $service->appId;//授权方
                $collect->card_id = md5($user_id.'card_id-'.$user['referral_code'].time());;
                $collect->trans_hash = md5($user_id.'trans_hash-'.$user['referral_code'].time());;
                $collect->card_time = time();
                $collect->add = $user['addr'];//保存位置
                $collect->up_num = $goods['issue_num']??1;
                $collect->save();
            }
            Db::commit();
            return $collect;
        }catch (\Exception $e){
            Db::rollback();
            new Exception('操作失败');
        }

    }

    public  function getOne($id,$uid)
    {
        $data = self::get($id);
        if (!$data || ($uid && $data['user_id'] !=$uid)){
            new Exception('藏品不存在');
        }
        if ($data['status']>1){
            new Exception('藏品'.$this->status()[$data['status']]);
        }
        if ($data['is_consume'] == 1){
            new Exception('藏品不存在');
        }
        return $data;
    }

    public static function getList($params,$uid,$from='')
    {
        if ($from=='hall'){
            //寄售大厅
            $where = ['a.is_consume'=>0,'a.status'=>1];
            if (isset($params['from']) && $params['from']=='hall'){
                $where = ['a.user_id'=>['<>',$uid]];
            }
            if (isset($params['from']) &&$params['from']=='own'){
                $where = ['a.user_id'=>$uid,'a.status'=>1];
            }
        }else{
            $where = ['a.user_id'=>$uid, 'a.is_consume'=>0, 'a.status'=>['<',2]];
        }
        if (isset($params['cate_id'])&&$params['cate_id']){
            $where['sg.category_ids'] = $params['cate_id'];
        }
        if (isset($params['keywords'])&&$params['keywords']){
            $where['sg.title'] = ['like','%'.$params['keywords'].'%'];
        }
        $order = 'id desc';
        if (isset($params['order'])&&$params['order']){
            //最新发布
            if ($params['order'] == 'new'){
                $order = 'a.status_time desc';
            }
            //最优售价
            if ($params['order'] == 'price'){
                $order = 'a.price asc';
            }
        }
        $list = self::alias('a')
            ->field('a.id,a.goods_id,a.original_price,a.price,a.status,a.status_time,a.from_type,sg.title,sg.image,sc.name cate_name,sg.category_ids cate_id')
            ->join('shopro_goods sg','a.goods_id=sg.id')
            ->join('shopro_category sc','sg.category_ids=sc.id')
            ->where($where)
            ->order($order)
            ->paginate($params['limit']??20,false,['page'=>$params['page']??1]);

        return $list;
    }

    /**
     * 销毁
     * @param $adminId
     * @param $uid
     * @param $assetId
     * @param $shardId
     * @return mixed
     * @throws \think\exception\DbException
     */
    public static function consume($goodsId, $uid, $assetId, $shardId)
    {
        $goods = Goods::withTrashed()->where('id',$goodsId)->find();
        $adminId = $goods['admin_id']??0;
        if (!$assetId)return false;
        $service = new Service();
        //上链
        $admin = Db::name('admin')->where('id',$adminId)->find();
        $caccount = array(
            'address' => $admin['addr'],
            'public_key' => $admin['public_key'],
            'private_key' => $admin['private_key'],
        );
        $user = User::get($uid);
        $uaccount = array(
            'address' => $user['addr'],
            'public_key' => $user['public_key'],
            'private_key' => $user['private_key'],
        );
        return $service->consumeShard($caccount, $uaccount, $assetId, $shardId);
    }


    //资产转移
    public static function transferShard($goodsId, $ownId,$uid, $assetId, $shardId,$price=0)
    {
        $goods = Goods::withTrashed()->where('id',$goodsId)->find();
        if ($goods && $price==0){
            $price =  $goods['price'];
        }
        $service = new Service();
        //上链
        $own = User::get($ownId);
        $caccount = array(
            'address' => $own['addr'],
            'public_key' => $own['public_key'],
            'private_key' => $own['private_key'],
        );
        $user = User::get($uid);

//        $res = $service->queryShard($assetId, $shardId);
        return $service->transferShard($caccount, $assetId, $shardId, $user['addr'],$price*100, $uid);
    }
}
