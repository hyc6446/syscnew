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
                $collect->type = $type; // '获得方式:1=购买,2=合成,3=赠送,4=盲盒',
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
        if (!$data || $data['user_id'] !=$uid){
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

    public static function getList($params,$uid)
    {

        $where = ['a.user_id'=>$uid, 'a.is_consume'=>0, 'a.status'=>['<',2]];
        if (isset($params['category_id'])&&$params['category_id']){
            $where['a.category_ids'] = $params['category_id'];
        }
        $list = self::alias('a')
            ->field('a.id,a.goods_id,a.original_price,a.price,a.status,a.status_time,a.type,sg.title,sg.image,sc.name')
            ->join('shopro_goods sg','a.goods_id=sg.id')
            ->join('shopro_category sc','sg.category_ids=sc.id')
            ->where($where)
            ->paginate($params['limit']??20,false,['page'=>$params['page']??1]);

        return $list;
    }
}
