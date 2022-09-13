<?php

namespace addons\shopro\model;

use addons\shopro\exception\Exception;
use addons\xasset\library\Service;
use app\admin\model\Admin;
use app\admin\model\shopro\user\Collect;
use fast\Date;
use nft\Nfts;
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
        'image', 'status_text', 'type_text', 'status_time_text'
    ];

    protected $hidden =  ['give_user_id', 'querysds'];
    public function getImageAttr($value, $data)
    {
        if (!empty($value)) return cdnurl($value, true);
    }

    public function status()
    {
        return ['正常', '正在寄售', '已售出', '已合成', '已赠予'];
    }

    public function getStatusTextAttr($value, $data)
    {
        //0=正常,1=正在寄售,2=已售出,3=已合成,4=已赠予
        return $this->status()[$data['status']];
    }
    public function getTypeTextAttr($value, $data)
    {
        return ['1' => '购买', '2' => '合成', '3' => '赠送', '4' => '盲盒', '5' => '空投'][$data['from_type']];
    }

    public function getStatusTimeTextAttr($value, $data)
    {
        return $data['status_time'] ? date('Y-m-d H:i', $data['status_time']) : '';
    }
    public function getTimestampAttr($value, $data)
    {
        return $value ? date('Y-m-d H:i:s', strtotime($value) + 8 * 3600) : '';
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

            if (isset($id) && $id) {
                $collect = self::where(['user_id' => $user_id, 'id' => $id])->find();
                if ($collect) {
                    $collect->is_consume = $is_consume ?? 0; //链上 资产是否销毁
                    $collect->to_user_id = $to_user_id ?? 0; //赠予人
                    $collect->sn = $sn ?? 0; //编号
                    $collect->is_hook = $is_hook ?? 0; //是否锁定
                    $collect->status = $status ?? 0; //状态:0=正常,1=正在寄售,2=已售出,3=已合成,4=已赠予
                    if (isset($price)) $collect->price = $price ?? 0; //寄售价格
                    $collect->save();
                } else {
                    Db::rollback();
                    new Exception('藏品不存在');
                }
            } else {
                $user = User::get($user_id);
                $goods = Goods::withTrashed()->where('id', $goods_id)->find();
                $brand = GoodsBrand::where('id', 'in', explode(',', $goods['brand_ids']))->column('name');
                $collect = [];
                $collect['task_id'] = $task_id ?? ''; //上链的任务id
                $collect['operation_id'] = $operation_id ?? '';
                $collect['sn'] = $sn ?? 0; //编号
                $collect['is_hook'] = $is_hook ?? 0; //是否锁定
                //上链  转让nft和赠送nft  不在这里发行 ,单独接口处理
                if (!isset($notShard)  && $goods['asset_id'] && $user['addr']) {
                    if ($orderItem->activity_type == 'priority') {
                        // 批量铸造
                        $res = (new Nfts())->publishBatchNft($goods, $user['addr'], $orderItem->goods_num);
                    } else {
                        $res = (new Nfts())->publishNft($goods, $user['addr']);
                    }
                    Log::info('授予资产碎片:::::' . json_encode($res));
                    $collect['task_id'] = $res['data']['operation_id']; //上链的任务id
                    $collect['operation_id'] = $res['data']['operation_id']; //上链的任务id
                }

                if ($collect['operation_id']) {
                    //查询
                    $txRes = (new Nfts())->txOperationRes($collect['operation_id']);
                }
                $timestamp = $txRes['data']['timestamp'];
                $collect['trans_hash'] = $txRes['data']['tx_hash'] ?? '';
                $collect['class_id'] = $txRes['data']['class_id'] ?? '';
                $collect['message'] = $txRes['data']['message'] ?? '';
                $collect['block_height'] = $txRes['data']['block_height'] ?? '';
                $collect['timestamp'] = isset($timestamp) ? strtotime($timestamp) : '';
                $collect['tag'] = $txRes['data']['tag'] ?? '';
                $collect['type'] = $txRes['data']['type'] ?? '';
                $collect['wcl_status'] = $txRes['data']['status'] ?? '';
                //之前的字段
                $collect['querysds'] = json_encode($txRes['data']) ?? '';
                $collect['card_time'] = isset($timestamp) ? strtotime($timestamp) : 0;
                $collect['add'] = $txRes['data']['block_height'] ?? '';

                $collect['user_id'] = $user_id; //藏品所有者
                $collect['goods_id'] = $goods_id; //藏品id
                $collect['original_price'] = $goods['price'] ?? 0; //藏品原价格
                $collect['asset_id'] = $goods['asset_id'] ?? 0; //链上 NFT类别id
                $collect['give_user_id'] = $give_user_id ?? 0; //赠予人
                $collect['order_sn'] = $order_sn ?? 0;
                $collect['give_collect_id'] = $give_collect_id ?? 0; //赠予人
                $collect['to_user_id'] = $to_user_id ?? 0; //赠予人
                $collect['is_consume'] = 0; //链上 资产是否销毁
                $collect['owner_addr'] = $user['addr'] ?? ''; //资产账户地址
                $collect['status'] = 0; //状态:0=正常,1=正在寄售,2=已售出,3=已合成,4=已赠予
                $collect['from_type'] = $type;
                $collect['token'] = md5($user_id . 'token-' . $user['referral_code'] . time());
                $collect['up_brand'] = $brand ? implode('&', $brand) : '-';
                $collect['auth_brand'] = '文昌链Avata'; //授权方
                $collect['up_num'] = $goods['issue_num'] ?? 1;

                // if ($txRes['data']['type'] == "mint_nft_batch") {
                $nfts = explode(",", $txRes['data']['nft_id']);
                $collects = [];
                foreach ($nfts as $val) {
                    $collect['nft_id'] = $val ?? '';
                    $collect['card_id'] = $val ?? '';
                    $collect['shard_id'] = $val ?? '';
                    array_push($collects, $collect);
                }
                $self = new self();
                $self->saveAll($collects);
            }
            Db::commit();
            return $collect;
        } catch (\Exception $e) {
            Db::rollback();
            new Exception('操作失败' . $e->getMessage());
        }
    }

    public  function getOne($id, $uid)
    {
        $data = self::get($id);
        if (!$data) {
            new Exception('藏品不存在');
        }
        if ($data['status'] > 1) {
            new Exception('藏品' . $this->status()[$data['status']]);
        }
        if ($data['is_consume'] == 1) {
            new Exception('藏品不存在');
        }

        if ($data['wcl_status'] != 1 && $data['wcl_status'] != 2) {
            //查询
            $txRes = (new Nfts())->txOperationRes($data->operation_id);
            $data = self::setData($data, $txRes);
            // exit;
            $data->save();
            $data = self::get($id);
        }
        if (!$data['block_height']) {
            $data['block_height'] = $data['wcl_status'] == 1 ? '已上链' : ($data['wcl_status'] == 2 ? '交易失败' : '上链中');
        }

        return $data;
    }
    public static function setData($collect,$txRes)
    {
        if (!empty($txRes['data'])){
            $data = $txRes['data'];
            extract($data);
            // $collect->nft_id = $nft_id??'';
            $collect->trans_hash = $tx_hash??'';
            $collect->class_id = $class_id??'';
            $collect->message = $message??'';
            $collect->block_height = $block_height??'';
            $collect->timestamp = isset($timestamp) ? strtotime($timestamp) : '';
            $collect->tag = $tag??'';
            $collect->type = $type??'';
            $collect->wcl_status = $status??'';
            //之前的字段
            // $collect->querysds = $type??'';
            // $collect->card_id = $nft_id??'';
            // $collect->shard_id = $nft_id??'';//链上 碎片id
            $collect->card_time = isset($timestamp)?strtotime($timestamp):0;
            $collect->add = $block_height??'';
        }
        return $collect;
    }
    public static function getList($params, $uid, $from = '')
    {
        if ($from == 'hall') {
            //寄售大厅
            $where = ['a.is_consume' => 0, 'a.status' => 1];
            if (isset($params['from']) && $params['from'] == 'hall') {
                if (isset($uid)) {
                    $where['a.user_id'] = ['<>', $uid];
                }
            }
            if (isset($params['from']) && $params['from'] == 'own') {
                $where = ['a.is_consume' => 0, 'a.user_id' => $uid, 'a.status' => 1];
            }
        } else {
            $where = ['a.user_id' => $uid, 'a.is_consume' => 0, 'a.status' => ['<', 2]];
        }

        if (isset($params['cate_id']) && $params['cate_id']) {
            $where['sg.category_ids'] = $params['cate_id'];
        }
        if (isset($params['keywords']) && $params['keywords']) {
            $where['sg.title'] = ['like', '%' . $params['keywords'] . '%'];
        }
        if (isset($params['from_type']) && $params['from_type']) {
            $where['a.from_type'] = $params['from_type'];
        }
        $order = 'id desc';
        if (isset($params['order']) && $params['order']) {
            //最新发布
            if ($params['order'] == 'new') {
                $order = 'a.status_time desc';
            }
            //最优售价
            if ($params['order'] == 'price') {
                $order = 'a.price asc';
            }
        }
        $where['a.is_status'] = 1;
        $list = self::alias('a')
            ->field('a.id,a.goods_id,a.original_price,a.price,a.status,a.status_time,a.from_type,sg.title,sg.image,sc.name cate_name,sg.category_ids cate_id')
            ->join('shopro_goods sg', 'a.goods_id=sg.id')
            ->join('shopro_category sc', 'sg.category_ids=sc.id')
            ->where($where)
            ->order($order)
            ->paginate($params['limit'] ?? 20, false, ['page' => $params['page'] ?? 1]);

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
        $goods = Goods::withTrashed()->where('id', $goodsId)->find();
        $adminId = $goods['admin_id'] ?? 0;
        if (!$assetId) return false;
        $service = new Service();
        //上链
        $admin = Db::name('admin')->where('id', $adminId)->find();
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


    /**
     *
     * 转让 nft
     * @param $class_id  string NFT类别
     * @param $ownId int  所有者
     * @param $uid int 接收者
     * @return array
     * @throws \think\exception\DbException
     */
    public static function transferShard($class_id, $ownId, $uid, $nft_id)
    {
        //上链
        $own = User::get($ownId); //所有者
        $user = User::get($uid); //接收者
        $res = (new Nfts())->transferShard($class_id, $own['addr'], $nft_id, $user['addr']);
        Log::info('转让 NFT:::::' . json_encode($res));
        return $res;
    }

    public static function getGiveList($params, $uid)
    {
        $where['a.user_id'] = $uid;
        if (isset($params['type']) && $params['type']) {
            if ($params['type'] == 1) $where['a.to_user_id'] = ['>', 0];
            if ($params['type'] == 2) {
                $where['a.give_user_id'] = ['>', 0];
                $where['a.from_type'] = 3;
                $where['a.to_user_id'] = 0;
            }
        }
        $list = self::alias('a')
            ->field('a.id,a.status,a.status_time,a.from_type,sg.title,sg.image,gu.nickname,tu.nickname tu_nickname,gu.mobile,tu.mobile tu_mobile,a.wcl_status,a.give_collect_id')
            ->join('shopro_goods sg', 'a.goods_id=sg.id')
            ->join('user gu', 'a.give_user_id=gu.id', 'left')
            ->join('user tu', 'a.to_user_id=tu.id', 'left')
            ->where($where)
            ->where(function ($query) {
                $query->where(['a.give_user_id' => ['>', 0]])->whereOr(['a.to_user_id' => ['>', 0]]);
            })
            ->order('a.status_time desc')
            ->paginate($params['limit'] ?? 10, false, ['page' => $params['page'] ?? 1])->each(function ($item) {
                if ($item['status'] == 4) {
                    //已转出
                    $collect = self::where('give_collect_id', $item['id'])->find(); //查看来自
                    $item['give_txt'] = $collect['wcl_status'] == 0 || $collect['wcl_status'] == 3 ? '待接收' : '已转出';
                    $item['user_mobile'] = $item['tu_mobile']; //接受者
                    $item['user_name'] = $item['tu_nickname']; //接受者
                } else {
                    //转入
                    $item['give_txt'] = $item['wcl_status'] == 0 || $item['wcl_status'] == 3 ? '待接收' : '已转入';
                    $item['user_mobile'] = $item['mobile']; //转出者
                    $item['user_name'] = $item['nickname']; //转出者
                }
                unset($item['nickname'], $item['tu_nickname'], $item['mobile'], $item['tu_mobile']);
            });

        return $list;
    }
}
