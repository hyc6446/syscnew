<?php

namespace addons\shopro\model;

use addons\shopro\exception\Exception;
use think\Db;
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
        return ['1'=>'购买','2'=>'合成','3'=>'赠送','4'=>'盲盒'][$data['type']];
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
                $brand = GoodsBrand::where( 'id','in',explode(',',$goods['brand_ids']))->select();
                $sku =  Db::name('shopro_goods_sku_price')->where(['goods_id'=>$goods_id,'status'=>'up'])->field('stock,sales')->find();
                $sku = $sku['stock'] + $sku['sales'];//总库存
                $collect = new self();
                $collect->user_id = $user_id;//藏品所有者
                $collect->goods_id = $goods_id;//藏品id
                $collect->original_price = $original_price??0;//藏品原价格
                $collect->asset_id = $asset_id??0;//链上 资产id
                $collect->shard_id = $shard_id??0;//链上 碎片id
                $collect->give_user_id = $give_user_id??0;//赠予人
                $collect->is_consume = 0;//链上 资产是否销毁
                $collect->owner_addr = $owner_addr??'';//资产账户地址
                $collect->querysds = $querysds??'';//资产信息json
                $collect->status = 0;//状态:0=正常,1=正在寄售,2=已售出,3=已合成,4=已赠予
                $collect->type = $type;
                $collect->token = md5($user_id.'token-'.$user['referral_code'].time());
                $collect->up_brand = $brand?implode('&',$brand):'-';
                $collect->auth_brand = $brand?implode('&',$brand):'-';
                $collect->card_id = md5($user_id.'card_id-'.$user['referral_code'].time());;
                $collect->trans_hash = md5($user_id.'trans_hash-'.$user['referral_code'].time());;
                $collect->card_time = time();
                $collect->add = $user['referral_code'].time();
                $collect->up_num = $sku;
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
        if (isset($params['category_id'])&&$params['category_id']){
            $where['a.category_ids'] = $params['category_id'];
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
            ->field('a.id,a.goods_id,a.original_price,a.price,a.status,a.status_time,a.type,sg.title,sg.image,sc.name cate_name')
            ->join('shopro_goods sg','a.goods_id=sg.id')
            ->join('shopro_category sc','sg.category_ids=sc.id')
            ->where($where)
            ->order($order)
            ->paginate($params['limit']??20,false,['page'=>$params['page']??1]);

        return $list;
    }
}
