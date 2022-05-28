<?php

namespace addons\shopro\model;

use think\Model;
use traits\model\SoftDelete;
/**
 * 用户收藏模型
 */
class UserFavorite extends Model
{
    use SoftDelete;

    protected $name = 'shopro_user_favorite';
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $deleteTime = 'deletetime';
    protected $hidden = ['createtime', 'updatetime'];

    // 追加属性
    protected $append = [
    ];

    public static function edit($params)
    {
        extract($params);
        $user = User::info();
        //批量删除模式
        if (isset($goods_ids)) {
            foreach ($goods_ids as $g) {
                self::get(['goods_id' => $g, 'user_id' => $user->id])->delete();
            }
            return false;
        }
        //单商品默认反向增删
        $favorite = self::get(['goods_id' => $goods_id, 'user_id' => $user->id]);
        if ($favorite) {
            $favorite->delete();
            return false;
        }else{
            self::create([
                'user_id' => $user->id,
                'goods_id' => $goods_id
            ]);
            return true;
        }
    }

    public static function getGoodsList($page,$limit)
    {
        $user = User::info();

        // 商品物理删除的，直接删掉
        self::whereNotExists(function ($query) {
            $goodsTableName = (new Goods())->getQuery()->getTable();
            $tableName = (new self())->getQuery()->getTable();
            $query = $query->table($goodsTableName)->where($goodsTableName . '.id=' . $tableName . '.goods_id');

            return $query;
        })->where([
            'user_id' => $user->id
        ])->delete();

        $category_ids = [];
        if (isset($category_id) && $category_id != 0) {
            // 查询分类所有子分类,包括自己
            $category_ids = Category::getCategoryIds($category_id);
        }

        $query = self::where(function ($query) use ($category_ids) {
            // 所有子分类使用 find_in_set or 匹配，亲测速度并不慢
            foreach($category_ids as $key => $category_id) {
                $query->whereOrRaw("find_in_set($category_id, category_ids)");
            }
        });

//
//        $favoriteData = $query->with(['goods' => function ($query) {
//            $query->removeOption('soft_delete');
//            $query->field('id,title,status,image,price,service_ids,brand_ids,sales_time,tag,is_syn,can_sales,syn_end_time,category_ids');
//        }])->where([
//            'user_id' => $user->id
//        ])->order('createtime', 'DESC')->paginate(10);

        $favoriteData = $query->alias('a')
            ->field('a.*,bs.image,bs.title,bs.price,bs.content,sc.name category_name')
            ->join('shopro_goods bs','a.goods_id = bs.id')
            ->join('shopro_category sc','bs.category_ids = sc.id')
            ->where([
            'user_id' => $user->id
        ])->order('createtime', 'DESC')->paginate($limit,false,['page'=>$page])->each(function ($item){
                $item['desc'] = StringToText($item['content'],100);
                $item['image'] = cdnurl($item['image'],true);
                unset($item['content']);
                return $item;

            });

        return $favoriteData;
    }

    public function goods()
    {
        return $this->belongsTo(Goods::class, 'goods_id', 'id');
    }


}
