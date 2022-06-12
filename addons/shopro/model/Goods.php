<?php

namespace addons\shopro\model;

use app\common\library\Auth;
use think\Db;
use think\Model;
use addons\shopro\exception\Exception;
use addons\shopro\library\traits\model\goods\GoodsActivity;
use traits\model\SoftDelete;

/**
 * 藏品模型
 */
class Goods extends Model
{
    use SoftDelete, GoodsActivity;

    // 表名,不含前缀
    protected $name = 'shopro_goods';
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $deleteTime = 'deletetime';

    protected $hidden = ['createtime', 'updatetime', 'status','type','sales','show_sales', 'params', 'images','dispatch_type','dispatch_ids','is_sku','original_price','likes','subtitle'];
    //列表动态隐藏字段
    public static $list_hidden = ['weigh','note'];

    // 追加属性
    protected $append = [
        'ser_tag_arr','brand_arr','sales_time_text','tag','category_name','syn_end_time_text','sale_sta'
    ];

    public static $list_field = '*';


    public function getSerTagArrAttr($value, $data)
    {
        if (!isset($data['service_ids'])) return [];
        $tag = GoodsService::where( 'id','in',explode(',',$data['service_ids']))->select();
        $item = array_column($tag,'name');

        return $item;
    }

    public function getTagAttr($value, $data)
    {
        return isset($data['tag'])&&$data['tag']?explode(',',$data['tag']):[];
    }

    public function getSalesTimeTextAttr($value,$data)
    {
        return isset($data['sales_time'])&&$data['sales_time'] && $data['sales_time']>time()?date('m月d日 H:i'):'';
    }

    public function getSynEndTimeTextAttr($value,$data)
    {
        return  isset($data['syn_end_time'])&&$data['syn_end_time']?date('m月d日 H:i'):'';
    }

    public function getBrandArrAttr($value, $data)
    {
        if (!isset($data['brand_ids'])) return [];
        return GoodsBrand::where( 'id','in',explode(',',$data['brand_ids']))->select();
    }

    public function getStockSkuAttr($value, $data)
    {
        $sku =  Db::name('shopro_goods_sku_price')->where(['goods_id'=>$data['id'],'status'=>'up'])->field('stock,sales')->find();
        $sku['sell_out'] = 0;
        if (!$sku['stock']) $sku['sell_out'] = 1;
        $sku['stock_limit'] = $sku['stock']+ $sku['sales'];//总库存
        unset($sku['sales']);
        return $sku;
    }

    public function getCategoryNameAttr($value,$data)
    {
        if (!isset($data['category_ids']))return '';
       return Category::where('id',$data['category_ids'])->value('name');
    }

    public function getSaleStaAttr($value,$data)
    {
        $sku =  Db::name('shopro_goods_sku_price')->where(['goods_id'=>$data['id'],'status'=>'up'])->field('stock,sales')->find();
        if ($data['issue']==0) return ['status'=>0,'txt'=>'即将发售'];

        if ($sku['stock']==0){
            return ['status'=>2,'txt'=>'已售罄'];
        }elseif ($data['sales_time']>time()){
            return ['status'=>0,'txt'=>'即将发售'];
        }elseif ($data['can_sales']==1 && $data['sales_time']<=time()){
            return ['status'=>1,'txt'=>'正在发售'];
        }else{
            return ['status'=>0,'txt'=>'即将发售'];
        }
    }

    /**
     * 藏品列表
     * params 请求参数
     * is_page 是否分页
     * per_sale 是否是预售藏品
     */
    public static function getGoodsList($params, $is_page = true,$per_sale=0)
    {
        extract($params);
        $where = [
            'status' => ['in', ((isset($type) && $type == 'all') ? ['up', 'hidden'] : ['up'])],     // type = all 查询全部
            'issue'=>1,
        ];
        if ((isset($islist) && $islist) && (isset($tag) && $tag != 'select')){
            $where['can_sales'] = 1;
            $where['sales_time'] = ['<=',time()];
        }
        //排序字段
        if (isset($order)) {
            $order = self::getGoodsListOrder($order);

        }else{
            $order = 'weigh desc';
        }

        if (isset($keywords) && $keywords !== '') {
            $where['title|subtitle'] = ['like', "%$keywords%"];
        }

        if (isset($goods_ids) && $goods_ids !== '') {
            $order = 'field(id, ' . $goods_ids . ')';       // 如果传了 goods_ids 就按照里面的 id 进行排序
            $goodsIdsArray = explode(',', $goods_ids);
            $where['id'] = ['in', $goodsIdsArray];
        }

        if (isset($is_syn) && $is_syn !== ''){
            //查询合成品列表
            $where['is_syn'] = $is_syn;
            if ($is_syn == 1){
                $where['syn_end_time'] = [['>',time()],['=',0],'or'];
                $where['children'] = ['<>',''];
                $goodsIdsArray = GoodsSkuPrice::where('stock','>',0)->column('goods_id');
                $where['id'] = ['in', $goodsIdsArray];
            }
        }

        $per_sale = $per_sale??0;
        if ($per_sale){
            $where['sales_time'] = ['>',time()];
        }

        $category_ids = [];
        if (isset($category_id) && $category_id != 0) {
            // 查询分类所有子分类,包括自己
            $category_ids = Category::getCategoryIds($category_id);
        }
        $goods = self::where($where)->where(function ($query) use ($category_ids) {
            // 所有子分类使用 find_in_set or 匹配，亲测速度并不慢
            foreach($category_ids as $key => $category_id) {
                $query->whereOrRaw("find_in_set($category_id, category_ids)");
            }
        });

        // 过滤有活动的藏品
        if (isset($no_activity) && $no_activity) {
            $goods = $goods->whereNotExists(function ($query) use ($where) {
                $activityTableName = (new Activity())->getQuery()->getTable();
                $goodsTableName = (new self())->getQuery()->getTable();
                $query->table($activityTableName)->where("find_in_set(" . $goodsTableName . ".id, goods_ids)")->where('deletetime', 'null');        // 必须手动加上 deletetime = null
            });
        }

        //标志
        if (isset($tag) && $tag !== '') {
            $goods = $goods->where("find_in_set('".$tag."',tag)");
        }


        $goods = $goods->field(self::$list_field.',(sales + show_sales) as total_sales')->orderRaw($order)->order('id desc');


        $hidden = self::$list_hidden;
        if (!$is_page||$per_sale){
            //发售日历
            $goods = $goodsData = $goods->select();
        }else{
            $hidden[] = 'content';
            $goods = $goods->paginate($limit ?? 10);
            $goodsData = $goods->items();
        }

        $data = [];
        if ($goodsData) {
            $collection = collection($goodsData);
            $data = $collection->hidden($hidden);
        }

        if (!$is_page||$per_sale){
            $sales = [];
            foreach ($data as $val){
                $date = date('m月d日 H:i',$val['sales_time']);
                $val['desc'] = StringToText($val['content'],100);
                unset($val['content']);
                $sales[$date][] = $val;
            }
            if ($per_sale){
                //发售日历
                $goods = [];
                $auth = Auth::instance();
                foreach ($sales as $key=>$value){
                    //是否订阅
                    $timestamp = strtotime($key);
                    $ding =  GoodsDing::where(['user_id'=>$auth->id??0,'ding_time'=>$timestamp])->find();
                    $goods[] = [
                        'date'=>mb_substr($key,0,6),
                        'time'=>mb_substr($key,7),
                        'ding_time'=>$value[0]['sales_time'],
                        'status'=>$ding?1:0,
                        'list'=>$value
                    ];
                }
            }else{
                $goods = $data;
            }

        } else {
            foreach ($data as &$val){
                //简述
                $val['desc'] = StringToText($val['content'],100);
                unset($val['content']);
            }
            $goods->data = $data;
        }

        return $goods;
    }

    public static function getGoodsListByIds($goodsIds)
    {
        $goodsIdsArray = explode(',', $goodsIds);
        $where = [
            'status' => 'up',
            'deletetime' => null,
            'id' => ['in', $goodsIdsArray]
        ];
        $goods = self::where($where)->paginate(10);

        if ($goods->items()) {
            $collection = collection($goods->items());
            $data = $collection->hidden(self::$list_hidden);

            // 处理活动
            // load_relation($data, 'skuPrice');        // 只针对数组
            $data->load('skuPrice');        // 延迟预加载
            foreach ($data as $key => $g) {
                $data[$key] = self::operActivity($g, $g['sku_price']);
            }

            $goods->data = $data;
        }
        return $goods;
    }

    public static function getFavoriteGoodsList($type = 'normal', $status = 'up')
    {
        $where = [
            'type' => $type,
            'status' => $status,
            'deletetime' => null,
        ];

        $goods = self::where($where)->paginate(10);

        if ($goods->items()) {
            $collection = collection($goods->items());
            $data = $collection->hidden(self::$list_hidden);
            $goods->data = $data;
        }
        return $goods;

    }


    // 获取秒杀藏品列表
    public static function getSeckillGoodsList($params) {
        extract($params);
        $type = $type ?? 'all';

        if ((new self)->hasRedis()) {
            // 如果有redis，读取 redis
            $activityList = (new self)->getActivityList(['seckill'], $type);
        } else {
            $where = [
                'type' => 'seckill'
            ];
            if ($type == 'ing') {
                $where['starttime'] = ['<', time()];
                $where['endtime'] = ['>', time()];
            } else if ($type == 'nostart') {
                $where['starttime'] = ['>', time()];
            } else if ($type == 'ended') {
                $where['endtime'] = ['<', time()];
            }

            $activityList = Activity::where($where)->select();
        }

        // 获取所有藏品 id
        $goodsIds = '';
        foreach ($activityList as $key => $activity) {
            $goodsIds .= ',' . $activity['goods_ids'];
        }

        if ($goodsIds) {
            $goodsIds = trim($goodsIds, ',');
        }

        $goodsList = self::getGoodsListByIds($goodsIds);

        return $goodsList;
    }


    // 获取拼团藏品列表
    public static function getGrouponGoodsList($params) {
        extract($params);
        $type = 'ing';

        if ((new self)->hasRedis()) {
            // 如果有redis，读取 redis
            $activityList = (new self)->getActivityList(['groupon'], $type);
        } else {
            $where = [
                'type' => 'groupon'
            ];
            if ($type == 'ing') {
                $where['starttime'] = ['<', time()];
                $where['endtime'] = ['>', time()];
            }

            $activityList = Activity::where($where)->select();
        }

        // 获取所有藏品 id
        $goodsIds = '';
        foreach ($activityList as $key => $activity) {
            $goodsIds .= ',' . $activity['goods_ids'];
        }

        if ($goodsIds) {
            $goodsIds = trim($goodsIds, ',');
        }

        $goodsList = self::getGoodsListByIds($goodsIds);

        return $goodsList;
    }


    /**
     * @param $id 藏品id
     * @param false $withTrashed  是否查询已经删除的藏品(针对用户已经收集成功后的展示)
     * @return array|bool|\PDOStatement|string|Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function getGoodsDetail($id,$withTrashed=false)
    {
        $user = User::info();
        $model = (new self());
        if ($withTrashed){
            //获取包括软删除的数据
            $model = self::withTrashed();
        }
        $detail =$model->field('*')
            ->where('id', $id)->with(['favorite' => function ($query) use ($user) {
            $user_id = empty($user) ? 0 : $user->id;
            return $query->where(['user_id' => $user_id]);
        }])->find();

        if (!$detail || ($detail->status === 'down' && !$withTrashed)) {
            new Exception('藏品不存在或已下架');
        }
        if (!$detail->issue) {
            new Exception('藏品暂未发行');
        }
        
//        $detail = $detail->append(['sku', 'coupons']);

        // 处理活动规格
        $detail = self::operActivity($detail, $detail->sku_price);

//        $detail['sku_price'] = $detail->sku_price;
        $detail['is_favorite']  =  $detail['favorite']?1:0;
        $detail['is_while_sales']  = 1;
        if (!$detail['sales_time'] || $detail['sales_time']<= time() || $withTrashed == true){
            $detail['is_while_sales'] = 0;
            $detail['sales_time'] = 0;
            $detail['can_sales'] = 1;
        }
        if ($detail['sales_time'] && $detail['sales_time']> time()){
            $detail['can_sales'] = 0;
        }
        unset($detail['favorite']);
        return $detail;
    }


    /**
     * 获取自提点
     */
    public static function getGoodsStore($params) {
        $user = User::info();

        $id = $params['id'] ?? 0;
        $keyword = $params['keyword'] ?? '';
        $latitude = $params['latitude'] ?? 0;
        $longitude = $params['longitude'] ?? 0;
        $per_page = $params['per_page'] ?? 10;

        $detail = (new self)->where('id', $id)->find();
        if (!$detail) {
            new Exception('藏品不存在');
        }

        if (strpos($detail['dispatch_type'], 'selfetch') === false) {
            new Exception('藏品不支持自提');
        }

        // 藏品支持自提，查询自提模板
        $dispatch = Dispatch::where('type', 'selfetch')->where('id', 'in', $detail['dispatch_ids'])->find();
        if (!$dispatch) {
            new Exception('自提模板不存在');
        }

        $dispatchSelfetch = DispatchSelfetch::where('id', 'in', $dispatch['type_ids'])->order('id', 'asc')->find();
        if (!$dispatchSelfetch) {
            new Exception('自提模板不存在');
        }
        
        // 查询自提点
        $selfetch = Store::show()->where('selfetch', 1);
        if ($dispatchSelfetch['store_ids']) {
            // 部分门店
            $selfetch = $selfetch->where('id', 'in', $dispatchSelfetch['store_ids']);
        }
        if ($latitude && $longitude) {
            $selfetch = $selfetch->field('*, ' . getDistanceBuilder($latitude, $longitude))->order('distance', 'asc');
        }

        if ($keyword) {
            $selfetch = $selfetch->where('name', 'like', '%' . $keyword . '%');
        }

        $selfetch = $selfetch->paginate($per_page);

        return $selfetch;
    }


    /**
     * 获取藏品购买人
     *
     * @param integer $goods_id
     * @param integer $activity_id
     * @return array
     */
    protected static function getGoodsBuyers($goods_id = 0, $activity_id = 0) {
        $where = [
            'goods_id' => $goods_id
        ];

        if ($activity_id) {
            // 是否查询指定活动的购买人
            $where['activity_id'] = $activity_id;
        }

        // 查询活动正在购买的人Goods
        $orderItems = OrderItem::with(['user' => function ($query) {
            return $query->field('id,nickname,avatar');
        }])->whereExists(function ($query) {
            $order_table_name = (new Order())->getQuery()->getTable();
            $table_name = (new OrderItem())->getQuery()->getTable();
            $query->table($order_table_name)->where($table_name . '.order_id=' . $order_table_name . '.id')->where('status', '>', 0);
        })->field('user_id')->where($where)->group('user_id')->limit(3)->select();

        $user = [];
        foreach ($orderItems as $item) {
            if ($item['user']) {
                $user[] = $item['user'];
            }
        }

        return $user;
    }


    public function getCouponsAttr($value, $data)
    {
        $user = User::info();
        $goods_id = $data['id'];

        // 只查可以领取的
        $where = [
            'gettimestart' => ['elt', time()],
            'gettimeend' => ['egt', time()]
        ];
        $coupons = Coupons::where(function ($query) use ($goods_id) {
            $query->where('find_in_set(' . $goods_id . ',goods_ids)')
            ->whereOr('goods_ids', 0);
        });

        if ($user) {
            // 关联用户状态
            $coupons = $coupons->with(['userCoupons' => function ($query) use ($user) {
                $query->where('user_id', $user->id);
            }]);
        }
        $coupons = $coupons->where($where)->select();

        // 判断优惠券，当前用户领取状态
        foreach ($coupons as &$coupon) {
            if ($user && $coupon->limit <= count($coupon->user_coupons)) {
                $coupon->status_code = 'cannot_get';
                $coupon->status_name = '已领取';
            } else {
                $coupon->status_code = 'can_get';
                $coupon->status_name = '可以领取';
            }
        }
        return $coupons;
    }


    protected function getSkuAttr($value, $data)
    {
        $sku = GoodsSku::all([
            'goods_id'=>$data['id'],
            'pid' => 0,
        ]);
        foreach ($sku as $s => &$k) {
            $sku[$s]['content'] = GoodsSku::all([
                'goods_id' => $data['id'],
                'pid' => $k['id']
            ]);
        }
        return $sku;
    }

    private static function getSkuPrice($value, $data)
    {
        return GoodsSkuPrice::all([
            'goods_id' => $data['id'],
            'status' => 'up',
            'deletetime' => null
        ]);
    }


    public function getParamsAttr($value, $data)
    {
        return $value ? json_decode($value, true) : [];
    }


    public function getServiceAttr($value, $data)
    {
        $value = $data['service_ids'];
        $serviceData = [];
        if (!empty($value)) {
            $serviceArray = explode(',', $value);
            $serviceData = [];
            foreach ($serviceArray as $v) {
                $serviceData[] = \addons\shopro\model\GoodsService::get($v);
            }
        }
        return $serviceData;
    }

    public function getImageAttr($value, $data)
    {
        if (!empty($value)) return cdnurl($value, true);

    }

    public function getImagesAttr($value, $data)
    {
        $imagesArray = [];
        if (!empty($value)) {
            $imagesArray = explode(',', $value);
            foreach ($imagesArray as &$v) {
                $v = cdnurl($v, true);
            }
            return $imagesArray;
        }
        return $imagesArray;
    }


    public function getContentAttr($value, $data)
    {
        $content = $data['content'];
        $content = str_replace("<img src=\"/uploads", "<img style=\"width: 100%;!important\" src=\"" . cdnurl("/uploads", true), $content);
        $content = str_replace("<video src=\"/uploads", "<video style=\"width: 100%;!important\" src=\"" . cdnurl("/uploads", true), $content);
        return $content;

    }


    public function getDispatchTypeArrAttr($value, $data)
    {
        return array_filter(explode(',', $data['dispatch_type']));
    }

    public function favorite()
    {
        return $this->hasOne(\addons\shopro\model\UserFavorite::class, 'goods_id', 'id');
    }


    public function scoreGoodsSkuPrice()
    {
        return $this->hasMany(\addons\shopro\model\scoreGoodsSkuPrice::class, 'goods_id', 'id')
            ->where('status', 'up')->order('id', 'asc');
    }


    public function skuPrice()
    {
        return $this->hasMany(\addons\shopro\model\GoodsSkuPrice::class, 'goods_id', 'id')
                ->order('id', 'asc');
    }

    //藏品列表排序
    private static function getGoodsListOrder($orderStr)
    {
        $order = 'weigh desc';
        $orderList = json_decode(htmlspecialchars_decode($orderStr), true);
        extract($orderList);
        if (isset($defaultOrder) && $defaultOrder === 1) {
            $order = 'weigh desc';
        }
        if (isset($priceOrder) && $priceOrder === 1) {
            $order = "convert(`price`, DECIMAL(10, 2)) asc";
        }elseif (isset($priceOrder) && $priceOrder === 2) {
            $order = "convert(`price`, DECIMAL(10, 2)) desc";
        }
        if (isset($salesOrder) && $salesOrder === 1){
            $order = 'total_sales desc';
        }
        if (isset($newProdcutOrder) && $newProdcutOrder === 1){
            $order = 'id desc';
        }
        return $order;

    }


    public  function composeList($params,$uid)
    {
        $goodsList = self::getGoodsList(array_merge($params, ['is_syn' => 1]));
        $collection = collection($goodsList->items());
        $goodsList->data = $collection->visible(['id','children','title','image','list','syn_end_time']);
        foreach ($goodsList as &$val){
            if (!$val['children']){
                unset($val);
                continue;
            }
            $val->append = ['category_name','syn_end_time_text'];
            $list = self::alias('a')
                ->field('a.id,a.title,a.image,ifnull(sa.id,0) own')
                ->join('shopro_user_collect sa','a.id=sa.goods_id and sa.status<2 and sa.user_id='.$uid,'left')
                ->whereIn('a.id',explode(',',$val['children']))
                ->where('a.status','up')
                ->select();
            foreach ($list as &$v){
                $v['own'] = $v['own']?1:0;
                $v->unsetAppend();
            }
            $val->list = $list;
        }
        return $goodsList;
    }

    public function compose($goodsId,$uid)
    {
        $goods =self::alias('a')
            ->field('a.*,sa.stock')
            ->join('shopro_goods_sku_price sa','a.id=sa.goods_id','left')
            ->where('a.id',$goodsId)
            ->where('a.status','up')
            ->find();
        if (!$goods){
            new Exception('藏品不存在或已下架');
        }
        if ($goods['stock']<1){
            new Exception('藏品已售罄');
        }
        if ($goods['syn_end_time']<time() && $goods['syn_end_time']>0){
            new Exception('已超过合成期限');
        }

        $children = self::whereIn('id',explode(',',$goods['children']))->where('status','up')->column('id');
        if (!$children){
            new Exception('该藏品无需子藏品合成');
        }

        $goodsChildren = UserCollect::where(['status'=>['<',2],'user_id'=>$uid])->whereIn('goods_id',$children)->column('id');
        if (count($goodsChildren)!=count($children)){
            new Exception('请先收集完所需藏品');
        }

        //todo::上链
        $res = UserCollect::edit([
            'user_id'=>$uid,
            'goods_id'=>$goodsId,
            'original_price'=>$goods['price'],
            'type'=>2,
        ]);
        if (!$res) new Exception('合成失败');
        UserCollect::whereIn('id',$goodsChildren)->update(['status'=>3,'status_time'=>time()]);
        return true;

    }
}
