<?php

namespace app\admin\controller\shopro\goods;

use addons\shopro\model\Category;
use app\admin\model\Admin;
use app\admin\model\shopro\activity\Activity;
use app\common\controller\Backend;
use app\common\model\Attachment;
use fast\Tree;
use think\Db;
use think\exception\PDOException;
use think\exception\ValidateException;
use Exception;
use addons\shopro\library\traits\StockWarning;
use think\Model;

/**
 * 商品
 *
 * @icon fa fa-circle-o
 */
class Goods extends Backend
{

    use StockWarning;

    /**
     * Goods模型对象
     * @var \app\admin\model\shopro\goods\Goods
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\shopro\goods\Goods;
        $this->view->assign("typeList", $this->model->getTypeList());
        $this->view->assign("statusList", $this->model->getStatusList());
        $this->view->assign("dispatchTypeList", $this->model->getDispatchTypeList());
    }

    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */
    public function selectpage()
    {
        //设置过滤方法
        $this->request->filter(['trim', 'strip_tags', 'htmlspecialchars']);

        //搜索关键词,客户端输入以空格分开,这里接收为数组
        $word = (array)$this->request->request("q_word/a");
        //当前页
        $page = $this->request->request("pageNumber");
        //分页大小
        $pagesize = $this->request->request("pageSize");
        //搜索条件
        $andor = $this->request->request("andOr", "and", "strtoupper");
        //排序方式
        $orderby = (array)$this->request->request("orderBy/a");
        //显示的字段
        $field = $this->request->request("showField");
        //主键
        $primarykey = $this->request->request("keyField");
        //主键值
        $primaryvalue = $this->request->request("keyValue");
        //搜索字段
        $searchfield = (array)$this->request->request("searchField/a");
        //自定义搜索条件
        $custom = (array)$this->request->request("custom/a");
        //是否返回树形结构
        $istree = $this->request->request("isTree", 0);
        $ishtml = $this->request->request("isHtml", 0);
        if ($istree) {
            $word = [];
            $pagesize = 999999;
        }
        $order = [];
        foreach ($orderby as $k => $v) {
            $order[$v[0]] = $v[1];
        }
        $field = $field ? $field : 'name';
        $is_show = $this->request->request('is_show');
        $is_show = isset($is_show)?$is_show:'';

        //如果有primaryvalue,说明当前是初始化传值
        if ($primaryvalue !== null) {
            $where = [$primarykey => ['in', $primaryvalue]];
            $pagesize = 999999;
        } else {
            $where = function ($query) use ($word, $andor, $field, $searchfield, $custom,$is_show) {
                $logic = $andor == 'AND' ? '&' : '|';
                $searchfield = is_array($searchfield) ? implode($logic, $searchfield) : $searchfield;
                $searchfield = str_replace(',', $logic, $searchfield);
                $word = array_filter(array_unique($word));
                if (count($word) == 1) {
                    $query->where($searchfield, "like", "%" . reset($word) . "%");
                } else {
                    $query->where(function ($query) use ($word, $searchfield) {
                        foreach ($word as $index => $item) {
                            $query->whereOr(function ($query) use ($item, $searchfield) {
                                $query->where($searchfield, "like", "%{$item}%");
                            });
                        }
                    });
                }
                if ($custom && is_array($custom)) {
                    foreach ($custom as $k => $v) {
                        if (is_array($v) && 2 == count($v)) {
                            $query->where($k, trim($v[0]), $v[1]);
                        } else {
                            $query->where($k, '=', $v);
                        }
                    }
                }

            };
        }
        $adminIds = $this->getDataLimitAdminIds();
        if (is_array($adminIds)) {
            $this->model->where($this->dataLimitField, 'in', $adminIds);
        }
        $list = [];
        $where1 = [];
        if($is_show==='0'){
            $where1['is_show'] = 0;
        }

        $total = $this->model->where($where)->where($where1)->count();
        if ($total > 0) {
            if (is_array($adminIds)) {
                $this->model->where($this->dataLimitField, 'in', $adminIds);
            }

            $fields = is_array($this->selectpageFields) ? $this->selectpageFields : ($this->selectpageFields && $this->selectpageFields != '*' ? explode(',', $this->selectpageFields) : []);

            //如果有primaryvalue,说明当前是初始化传值,按照选择顺序排序
            if ($primaryvalue !== null && preg_match("/^[a-z0-9_\-]+$/i", $primarykey)) {
                $primaryvalue = array_unique(is_array($primaryvalue) ? $primaryvalue : explode(',', $primaryvalue));
                //修复自定义data-primary-key为字符串内容时，给排序字段添加上引号
                $primaryvalue = array_map(function ($value) {
                    return '\'' . $value . '\'';
                }, $primaryvalue);

                $primaryvalue = implode(',', $primaryvalue);

                $this->model->orderRaw("FIELD(`{$primarykey}`, {$primaryvalue})");
            } else {
                $this->model->order($order);
            }

            $datalist = $this->model->where($where)->where($where1)
                ->page($page, $pagesize)
                ->select();

            foreach ($datalist as $index => $item) {
                unset($item['password'], $item['salt']);
                if ($this->selectpageFields == '*') {
                    $result = [
                        $primarykey => isset($item[$primarykey]) ? $item[$primarykey] : '',
                        $field      => isset($item[$field]) ? $item[$field] : '',
                    ];
                } else {
                    $result = array_intersect_key(($item instanceof Model ? $item->toArray() : (array)$item), array_flip($fields));
                }
                $result['pid'] = isset($item['pid']) ? $item['pid'] : (isset($item['parent_id']) ? $item['parent_id'] : 0);
                $list[] = $result;
            }
            if ($istree && !$primaryvalue) {
                $tree = Tree::instance();
                $tree->init(collection($list)->toArray(), 'pid');
                $list = $tree->getTreeList($tree->getTreeArray(0), $field);
                if (!$ishtml) {
                    foreach ($list as &$item) {
                        $item = str_replace('&nbsp;', ' ', $item);
                    }
                    unset($item);
                }
            }
        }
        //这里一定要返回有list这个字段,total是可选的,如果total<=list的数量,则会隐藏分页按钮
        return json(['list' => $list, 'total' => $total]);
    }

    /**
     * 查看
     */
    public function index()
    {
        //当前是否为关联查询
        $this->relationSearch = false;
        //设置过滤方法
        $this->request->filter(['strip_tags', 'trim']);
        if ($this->request->isAjax()) {
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField')) {
                return $this->selectpage();
            }
            // list($where, $sort, $order, $offset, $limit) = $this->buildparams('title');
            $sort = $this->request->get("sort", !empty($this->model) && $this->model->getPk() ? $this->model->getPk() : 'id');
            $sort = $sort == 'price' ? 'convert(`price`, DECIMAL(10, 2))' : $sort;
            $order = $this->request->get("order", "DESC");
            $offset = $this->request->get("offset", 0);
            $limit = $this->request->get("limit", 0);
            $activity_type = $this->request->get("activity_type", 'all');   // 活动类型

            $total = $this->buildSearchOrder()->count();

            // 构建查询数据条件
            $list = $this->buildSearchOrder();

            $subsql = \app\admin\model\shopro\goods\SkuPrice::where('status', 'up')->field('sum(stock) as stock, goods_id as sku_goods_id')->group('goods_id')->buildSql();
            $goodsTableName = $this->model->getQuery()->getTable();
            // 关联规格表，获取总库存
            $list = $list->join([$subsql => 'w'], $goodsTableName . '.id = w.sku_goods_id', 'left');

            // 关联查询当前商品的活动，一个商品可能存在多条活动记录，使用 group_concat 搜集所有活动类型，关联条件 只有 find_in_set 会存在一个商品出现多次，所以使用 group
            $actSubSql = \app\admin\model\shopro\activity\Activity::where('starttime', '<=', time())->where('endtime', '>=', time())->buildSql();
            $list = $list->join([$actSubSql => 'act'], "(find_in_set(" . $goodsTableName . ".id, act.goods_ids) or act.goods_ids = '')", 'left');

            // 关联查询当前商品是否设置有积分
            $scoreSubSql = \app\admin\model\shopro\app\ScoreSkuPrice::field("'score' as app_type, goods_id as score_goods_id")->group('score_goods_id')->buildSql();
            $list = $list->join([$scoreSubSql => 'score'], $goodsTableName . '.id = score.score_goods_id', 'left');

            $adminSubSql = Admin::field("username as admin_name,id")->buildSql();
            $list = $list->join([$adminSubSql => 'admin'], $goodsTableName . '.admin_id = admin.id', 'left');
            // 关闭 sql mode 的 ONLY_FULL_GROUP_BY
            $oldModes = closeStrict(['ONLY_FULL_GROUP_BY']);

            $list = $list->field("$goodsTableName.*, w.*,score.*,group_concat(act.type) as activity_type, act.goods_ids,admin.admin_name")
                ->group('id')
                ->orderRaw($sort . ' ' . $order)
                ->limit($offset, $limit)
                ->select();

            // 关联活动的商品
            $goodsIds = array_column($list, 'children');
            $goodsIdsArr = [];
            foreach($goodsIds as $ids) {
                $idsArr = explode(',', $ids);
                $goodsIdsArr = array_merge($goodsIdsArr, $idsArr);
            }
            $goodsIdsArr = array_values(array_filter(array_unique($goodsIdsArr)));
            if ($goodsIdsArr) {
                // 查询商品
                $goods = $this->model->where('id', 'in', $goodsIdsArr)->field('id,image,title,asset_id,issue')->select();
                $goods = array_column($goods, null, 'id');
            }

            // 恢复 sql mode
            recoverStrict($oldModes);
            foreach ($list  as $row) {
                $row->visible(['id','is_show','admin_id','asset_id','admin_name', 'type','issue','issue_num', 'activity_id', 'children','activity_type', 'is_sku', 'app_type', 'title', 'status', 'weigh', 'category_ids', 'image', 'price', 'likes', 'views', 'sales', 'stock', 'show_sales', 'dispatch_type', 'updatetime']);
            }
            $list = collection($list)->toArray();
            foreach ($list  as $key => $row){
                $list[$key]['children_list'] = [];
                $idsArr = explode(',', $row['children']);
                foreach ($idsArr as $id) {
                    if (isset($goods[$id])) {
                        $list[$key]['children_list'][] = $goods[$id];
                    }
                }
            }
            $result = array("total" => $total, "rows" => $list);

            if ($this->request->get("page_type") == 'select') {
                return json($result);
            }

            return $this->success('操作成功', null, $result);
        }
        return $this->view->fetch();
    }

    /**
     * 添加
     */
    public function add()
    {
        if ($this->request->isPost()) {
            $params = $this->request->post("row/a");
            $sku = $this->request->post("sku/a");

            if ($params) {
                $params = $this->preExcludeFields($params);

                if (!$params['is_sku']) {
                    // 单规格，price 必须是数字
                    if (!preg_match('/^[0-9]+(.[0-9]{1,8})?$/', $params['price'])) {
                        $this->error("请填写正确的价格");
                    }
                }

                if ($this->dataLimit && $this->dataLimitFieldAutoFill) {
                    $params[$this->dataLimitField] = $this->auth->id;
                }
                $result = false;
                Db::startTrans();
                try {
                    $params['issue_num'] = $params['stock'];
                    $params['issue'] = 1;
                    $params['admin_id'] = $this->auth->id;
                    $cate = Category::get($params['category_ids']);
                    $params['asset_id'] =$cate ['class_id'];
                    if ($cate['wcl_status']!=1){
                        new Exception('该分类还未上链成功,请重新选择');
                    }
                    if ($params['is_show']==0){
                        $params['sales_time'] = 0;
                    }
                    $result = $this->model->validateFailException(true)->validate('\app\admin\validate\shopro\Goods.add')->allowField(true)->save($params);
                    if ($result) {
                        $this->editSku($this->model, $sku, 'add');
                        Db::commit();
                    }

                } catch (ValidateException $e) {
                    Db::rollback();
                    $this->error($e->getMessage());
                } catch (PDOException $e) {
                    Db::rollback();
                    $this->error($e->getMessage());
                } catch (Exception $e) {
                    Db::rollback();
                    $this->error($e->getMessage());
                }
                if ($result !== false) {
                    $this->success("添加成功");
                } else {
                    $this->error(__('No rows were inserted'));
                }
            }
            $this->error(__('Parameter %s can not be empty', ''));
        }
        return $this->view->fetch();
    }



    /**
     * 查看详情
     */
    public function detail($ids = null) {
        $row = $this->model->get($ids);
        if (!$row) {
            $this->error(__('No Results were found'));
        }
        $row->append(['category_ids_arr', 'params_arr', 'dispatch_group_ids_arr']);

        $result = [];

        if ($row['is_sku']) {
            $skuList = \app\admin\model\shopro\goods\Sku::all(['pid' => 0, 'goods_id' => $ids]);
            if ($skuList) {
                foreach ($skuList as &$s) {
                    $s->children = \app\admin\model\shopro\goods\Sku::all(['pid' => $s->id, 'goods_id' => $ids]);
                }
            }
            $result['skuList'] = $skuList;

            $skuPrice = \app\admin\model\shopro\goods\SkuPrice::all(['goods_id' => $ids]);
            $result['skuPrice'] = $skuPrice;
        } else {
            // 将单规格的部分数据直接放到 row 上
            $goodsSkuPrice = \app\admin\model\shopro\goods\SkuPrice::where('goods_id', $ids)->order('id', 'asc')->find();

            $row['stock'] = $goodsSkuPrice['stock'] ?? 0;
            $row['sn'] = $goodsSkuPrice['sn'] ?? "";
            $row['weight'] = $goodsSkuPrice['weight'] ?? 0;
            $row['stock_warning'] = $goodsSkuPrice['stock_warning'];

            $result['skuList'] = [];
            $result['skuPrice'] = [];
            if ($row['issue']==1)$row['stock'] = $row['issue_num'];
        }
        $row['sales_time'] *= 1000;
        $row['syn_end_time'] *= 1000;


        $goods_ids_array = array_filter(explode(',', $row['children']));
        $goods_nums_array = array_filter(explode(',', $row->children_num));
        $goodsList = [];
        foreach ($goods_ids_array as $k => $g) {
            $goods[$k] = $this->model->field('id,title,image')->where('id', $g)->find();
            if ($goods[$k]){
                $goods[$k]['opt'] = 1;
                if($goods_nums_array[$k]){
                    $goods[$k]['nums'] = $goods_nums_array[$k];
                }else{
                    $goods[$k]['nums'] = 0;
                }
                $goodsList[] = $goods[$k];
            }
        }
        $row['goods_list'] = $goodsList;
        $result['detail'] = $row;

        return $this->success('获取成功', null, $result);
    }



    /**
     * 编辑
     */
    public function edit($ids = null)
    {
        if(!$ids) {
            $ids = $this->request->get('id');
        }
        $row = $this->model->get($ids);
        if (!$row) {
            $this->error(__('No Results were found'));
        }
//        if ($row['admin_id']!=$this->auth->id){
//            throw Exception('请勿操作他人创建的数字资产');
//        }
        $row->updatetime = time();

        $adminIds = $this->getDataLimitAdminIds();
        if (is_array($adminIds)) {
            if (!in_array($row[$this->dataLimitField], $adminIds)) {
                $this->error(__('You have no permission'));
            }
        }
        if ($this->request->isPost()) {
            $params = $this->request->post("row/a");
            $sku = $this->request->post("sku/a");

            if ($params) {
                // var_dump($params['children_num']);exit;
                $this->excludeFields = ['is_sku', 'type'];
                $params = $this->preExcludeFields($params);
                $result = false;
                Db::startTrans();
                try {
                    $params['issue'] = 1;
                    $params['issue_num'] = $params['stock'];
                    $cate = Category::get($params['category_ids']);
                    $params['asset_id'] =$cate ['class_id'];
                    if ($cate['wcl_status']!=1){
                        new Exception('该分类还未上链成功,请重新选择');
                    }
                    if ($params['is_show']==0){
                        $params['sales_time'] = 0;
                    }
                    $result = $row->validateFailException(true)->validate('\app\admin\validate\shopro\Goods.edit')->allowField(true)->save($params);
                    if ($result) {
                        $this->editSku($row, $sku, 'edit');
                        Db::commit();
                    }
                } catch (ValidateException $e) {
                    Db::rollback();
                    $this->error($e->getMessage());
                } catch (PDOException $e) {
                    Db::rollback();
                    $this->error($e->getMessage());
                } catch (Exception $e) {
                    Db::rollback();
                    $this->error($e->getMessage());
                }
                if ($result !== false) {
                    $this->success("编辑成功");
                } else {
                    $this->error(__('No rows were updated'));
                }
            }
            $this->error(__('Parameter %s can not be empty', ''));
        }
        $row['sales_time'] *= 1000;
        $row['syn_end_time'] *= 1000;

        $goods_ids_array = array_filter(explode(',', $row->children));
        $goodsList = [];
        foreach ($goods_ids_array as $k => $g) {
            $goods[$k] = $this->model->field('id,title,image')->where('id', $g)->find();
            $goods[$k]['opt'] = 1;
            $goodsList[] = $goods[$k];
        }
        $row->goods_list = $goodsList;
        $this->view->assign("row", $row);
        $skuList = \app\admin\model\shopro\goods\Sku::all(['pid' => 0, 'goods_id' => $ids]);
        if ($skuList) {
            foreach ($skuList as &$s) {
                $s->children = \app\admin\model\shopro\goods\Sku::all(['pid' => $s->id, 'goods_id' => $ids]);
            }
        }
        $this->assignconfig('skuList', $skuList);
        $skuPrice = \app\admin\model\shopro\goods\SkuPrice::all(['goods_id' => $ids]);
        $this->assignconfig('skuPrice', $skuPrice);
        return $this->view->fetch();
    }

    public function select()
    {
        if ($this->request->isAjax()) {
            return $this->index();
        }
        $categoryModel = new \app\admin\model\shopro\Category;
        $category = $categoryModel->with('children.children.children')->where('pid', 0)->order('weigh desc, id asc')->select();
        $this->assignconfig('category', $category);
        return $this->view->fetch();
    }


    public function setStatus($ids, $status) {
        if ($ids) {
            $pk = $this->model->getPk();
            $adminIds = $this->getDataLimitAdminIds();
            if (is_array($adminIds)) {
                $this->model->where($this->dataLimitField, 'in', $adminIds);
            }
            $list = $this->model->where($pk, 'in', $ids)->select();

            $count = 0;
            Db::startTrans();
            try {
                foreach ($list as $k => $v) {
                    $v->status = $status;
                    $count += $v->save();
                }
                Db::commit();
            } catch (PDOException $e) {
                Db::rollback();
                $this->error($e->getMessage());
            } catch (Exception $e) {
                Db::rollback();
                $this->error($e->getMessage());
            }
            if ($count) {
                $this->success();
            } else {
                $this->error(__('No rows were updated'));
            }
        }
        $this->error(__('Parameter %s can not be empty', 'ids'));
    }


    protected function editSku($goods, $sku, $type = 'add')
    {
        if ($goods['is_sku']) {
            // 多规格
            $this->editMultSku($goods, $sku, $type);
        } else {
            $this->editSimSku($goods, $sku, $type);
        }

    }


    /**
     * 添加编辑单规格
     */
    protected function editSimSku($goods, $sku, $type = 'add') {
        $params = $this->request->post("row/a");

        $data = [
            "goods_id" => $goods['id'],
            "stock" => $params['stock'] ?? 0,
            "stock_warning" => isset($params['stock_warning']) && is_numeric($params['stock_warning'])
                ? $params['stock_warning'] : null,
            "sn" => $params['sn'] ?? "",
            "weight" => $params['weight'] ? intval($params['weight']) : 0,
            "price" => $params['price'] ?? 0,
            "status" => 'up'
        ];

        if ($type == 'add') {
            $goodsSkuPrice = new \app\admin\model\shopro\goods\SkuPrice();
        } else {
            // 查询
            $goodsSkuPrice = \app\admin\model\shopro\goods\SkuPrice::where('goods_id', $goods['id'])->order('id', 'asc')->find();
            if (!$goodsSkuPrice) {
                $goodsSkuPrice = new \app\admin\model\shopro\goods\SkuPrice();
            }
        }

        $goodsSkuPrice->save($data);

        // 检测库存预警
        $this->checkStockWarning($goodsSkuPrice);
    }


    /**
     * 添加编辑多规格
     */
    protected function editMultSku($goods, $sku, $type = 'add') {
        $skuList = json_decode($sku['listData'], true);
        $skuPrice = json_decode($sku['priceData'], true);
        if (count($skuList) < 1) {
            throw Exception('请填写规格列表');
        }
        foreach ($skuList as $key => $sku) {
            if (count($sku['children']) <= 0) {
                throw Exception('主规格至少要有一个子规格');
            }

            // 验证子规格不能为空
            foreach ($sku['children'] as $k => $child) {
                if (!isset($child['name']) || empty(trim($child['name']))) {
                    throw Exception('子规格不能为空');
                }
            }
        }

        if (count($skuPrice) < 1) {
            throw Exception('请填写规格价格');
        }


        foreach ($skuPrice as &$price) {
            if (empty($price['price']) || $price['price'] == 0) {
                throw Exception('请填写规格价格');
            }
            if ($price['stock'] === '') {
                throw Exception('请填写规格库存');
            }
            if (empty($price['weight'])) {
                $price['weight'] = 0;
            }
        }

        // 编辑保存规格项
        $allChildrenSku = $this->saveSkuList($goods, $skuList, $type);

        if ($type == 'add') {
            // 创建新产品，添加规格列表和规格价格
            foreach ($skuPrice as $s3 => &$k3) {
                $k3['goods_sku_ids'] = $this->checkRealIds($k3['goods_sku_temp_ids'], $allChildrenSku);
                $k3['goods_id'] = $goods->id;
                $k3['goods_sku_text'] = implode(',', $k3['goods_sku_text']);
                $k3['weight'] = intval($k3['weight']);
                $k3['createtime'] = time();
                $k3['updatetime'] = time();

                unset($k3['id']);
                unset($k3['temp_id']);      // 前端临时 id
                unset($k3['goods_sku_temp_ids']);       // 前端临时规格 id,查找真实 id 用
            }
            $res = (new \app\admin\model\shopro\goods\SkuPrice)->allowField(true)->saveAll($skuPrice);

            // 检测库存预警
            $this->checkAllStockWarning($res, 'add');
        } else {
            // 编辑旧商品，先删除老的不用的 skuPrice
            $oldSkuPriceIds = array_column($skuPrice, 'id');
            // 删除当前商品老的除了在基础上修改的skuPrice
            \app\admin\model\shopro\goods\SkuPrice::where('goods_id', $goods->id)
                ->where('id', 'not in', $oldSkuPriceIds)->delete();

            // 删除失效的库存预警记录
            $this->delNotStockWarning($oldSkuPriceIds, $goods->id);

            foreach ($skuPrice as $s3 => &$k3) {
                $data['goods_sku_ids'] = $this->checkRealIds($k3['goods_sku_temp_ids'], $allChildrenSku);
                $data['goods_id'] = $goods->id;
                $data['goods_sku_text'] = implode(',', $k3['goods_sku_text']);
                $data['weigh'] = $k3['weigh'];
                $data['image'] = $k3['image'];
                $data['stock'] = $k3['stock'];
                $data['stock_warning'] = $k3['stock_warning'];
                $data['sn'] = $k3['sn'];
                $data['weight'] = intval($k3['weight']);
                $data['price'] = $k3['price'];
                $data['status'] = $k3['status'];
                $data['createtime'] = time();
                $data['updatetime'] = time();

                if ($k3['id']) {
                    // 编辑
                    $goodsSkuPrice = \app\admin\model\shopro\goods\SkuPrice::get($k3['id']);
                } else {
                    // 新增数据
                    $goodsSkuPrice = new \app\admin\model\shopro\goods\SkuPrice();
                }

                if ($goodsSkuPrice) {
                    $goodsSkuPrice->save($data);

                    // 检测库存预警
                    $this->checkStockWarning($goodsSkuPrice);
                }
            }
        }
    }


    // 根据前端临时 temp_id 获取真实的数据库 id
    private function checkRealIds($newGoodsSkuIds, $allChildrenSku)
    {
        $newIdsArray = [];
        foreach ($newGoodsSkuIds as $id) {
            $newIdsArray[] = $allChildrenSku[$id];
        }
        return implode(',', $newIdsArray);

    }


    // 差异更新 规格规格项（多的删除，少的添加）
    private function saveSkuList($goods, $skuList, $type = 'add') {
        $allChildrenSku = [];

        if ($type == 'edit') {
            // 删除无用老规格
            // 拿出需要更新的老规格
            $oldSkuIds = [];
            foreach ($skuList as $key => $sku) {
                $oldSkuIds[] = $sku['id'];

                $childSkuIds = [];
                if ($sku['children']) {
                    // 子项 id
                    $childSkuIds = array_column($sku['children'], 'id');
                }

                $oldSkuIds = array_merge($oldSkuIds, $childSkuIds);
                $oldSkuIds = array_unique($oldSkuIds);
            }

            // 删除老的除了在基础上修改的规格项
            \app\admin\model\shopro\goods\Sku::where('goods_id', $goods->id)->where('id', 'not in', $oldSkuIds)->delete();
        }

        foreach ($skuList as $s1 => &$k1) {
            //添加主规格
            if ($k1['id']) {
                // 编辑
                \app\admin\model\shopro\goods\Sku::where('id', $k1['id'])->update([
                    'name' => $k1['name'],
                ]);

                $skuId[$s1] = $k1['id'];
            } else {
                // 新增
                $skuId[$s1] = \app\admin\model\shopro\goods\Sku::insertGetId([
                    'name' => $k1['name'],
                    'pid' => 0,
                    'goods_id' => $goods->id
                ]);
            }
            $k1['id'] = $skuId[$s1];
            foreach ($k1['children'] as $s2 => &$k2) {
                if ($k2['id']) {
                    // 编辑
                    \app\admin\model\shopro\goods\Sku::where('id', $k2['id'])->update([
                        'name' => $k2['name'],
                    ]);

                    $skuChildrenId[$s1][$s2] = $k2['id'];
                } else {
                    $skuChildrenId[$s1][$s2] = \app\admin\model\shopro\goods\Sku::insertGetId([
                        'name' => $k2['name'],
                        'pid' => $k1['id'],
                        'goods_id' => $goods->id
                    ]);
                }

                $allChildrenSku[$k2['temp_id']] = $skuChildrenId[$s1][$s2];
                $k2['id'] = $skuChildrenId[$s1][$s2];
                $k2['pid'] = $k1['id'];
            }
        }

        return $allChildrenSku;
    }



    // 构建查询条件
    private function buildSearchOrder()
    {
        $search = $this->request->get("search", '');        // 关键字
        $status = $this->request->get("status", 'all');
        $activity_type = $this->request->get("activity_type", 'all');
        $app_type = $this->request->get("app_type", 'all');
        $min_price = $this->request->get("min_price", "");
        $max_price = $this->request->get("max_price", "");
        $category_id = $this->request->get('category_id', 0);
        $type = $this->request->get('type', '');
        $self = $this->request->get('self', '');
        $issue = $this->request->get('issue', '');

        $name = $this->model->getQuery()->getTable();
        $tableName = $name . '.';

        $goods = $this->model;

        if ($search) {
            // 模糊搜索字段
            $searcharr = ['title', 'id'];
            foreach ($searcharr as $k => &$v) {
                $v = stripos($v, ".") === false ? $tableName . $v : $v;
            }
            unset($v);
            $goods = $goods->where(function ($query) use ($searcharr, $search, $tableName) {
                $query->where(implode("|", $searcharr), "LIKE", "%{$search}%");
            });
        }

        $goods_ids = [];

        // 活动
        if ($activity_type != 'all') {
            // 同一请求，会组装两次请求条件,缓存 10 秒
            $activities = Activity::cache(10)->where('type', $activity_type)->column('goods_ids');
            foreach ($activities as $key => $goods_id) {
                $ids = explode(',', $goods_id);
                $goods_ids = array_merge($goods_ids, $ids);
            }
        }

        // 积分
        if ($app_type == 'score') {
            $score_goods_ids = \app\admin\model\shopro\app\ScoreSkuPrice::cache(10)->group('goods_id')->column('goods_id');
            $goods_ids = array_merge($goods_ids, $score_goods_ids);
        }

        $goods_ids = array_filter(array_unique($goods_ids));
        if ($goods_ids) {
            $goods = $goods->where($tableName . 'id', 'in', $goods_ids);
        } else {
            if ($activity_type != 'all' || $app_type != 'all') {
                // 搜了活动，但是 goods_ids 为空，这时候搜索结果应该为空
                $goods = $goods->where($tableName . 'id', 'in', $goods_ids);
            }
        }

        // 价格
        if ($min_price != '') {
            $goods = $goods->where('convert(`price`, DECIMAL(10, 2)) >= ' . round($min_price, 2));
        }
        if ($max_price != '') {
            $goods = $goods->where('convert(`price`, DECIMAL(10, 2)) <= ' . round($max_price, 2));
        }

        // 商品状态
        if ($status != 'all') {
            $goods = $goods->where('status', 'in', $status);
        }
        //合成品不能选择自己
        if ($self){
            $goods = $goods->where($tableName.'id','<>', $self);
        }
        //合成品选择商品只能从费合成品选
        if ($type=='hecheng'){
            $goods = $goods->where('is_syn', 0);
        }
        if ($issue){
            $goods = $goods->where('issue', $issue);
        }
        if(isset($category_id) && $category_id != 0) {
            $category_ids = [];
            // 查询分类所有子分类,包括自己
            $category_ids = \addons\shopro\model\Category::getCategoryIds($category_id);

            $goods = $goods->where(function ($query) use ($category_ids) {
                // 所有子分类使用 find_in_set or 匹配，亲测速度并不慢
                foreach($category_ids as $key => $category_id) {
                    $query->whereOrRaw("find_in_set($category_id, category_ids)");
                }
            });
        }

        return $goods;
    }


    public function createAsset($params,$add='add')
    {
        $admin = $this->auth->getUserInfo();
        $link = Attachment::where('url',$params['image'])->value('baidu_link');
        if (!$link) throw Exception('请重新上传图片');
        $account = array(
            'address' => $admin['addr'],
            'public_key' => $admin['public_key'],
            'private_key' => $admin['private_key'],
        );

        $service = new \addons\xasset\library\Service();
        $arrAssetInfo = array(
            'title' => $params['title'],
            'asset_cate' => 2,
            'thumb' => array($link),
            'short_desc' => $params['title'],
            'img_desc' => array($link),
            'asset_url' => array($link),
        );
        $strAssetInfo = json_encode($arrAssetInfo);
        $price = $params['price']*100;
        if ($add == 'add'){
            $assetId = gen_asset_id($service->appId);
            $userId = $this->auth->id;
            // 创造数字资产

            $res = $service->createAsset($account, $assetId, $params['issue_num'], $strAssetInfo, $price, $userId);
            if (isset($res['response']['errno']) &&$res['response']['errno']==0){
                return $res['response']['asset_id']??0;
            }
        }else{
            //修改未发行的数字资产
            $res = $service->alterAsset($account, $params['asset_id'],  $params['issue_num'], $strAssetInfo, $price);
            if (isset($res['response']['errno']) &&$res['response']['errno']==0){
                return true;
            }
        }
        throw Exception($add?'创建数字资产失败':'编辑数字资产失败');
    }


    //链上发行数字资产
    public function publishAsset($assetId)
    {
        $admin = $this->auth->getUserInfo();

        $service = new \addons\xasset\library\Service();
        $account = array(
            'address' => $admin['addr'],
            'public_key' => $admin['public_key'],
            'private_key' => $admin['private_key'],
        );
        $res = $service->publishAsset($account, (int)$assetId);
        if (isset($res['response']['errno']) && $res['response']['errno']==0){
            return true;
        }
        throw Exception('发行数字资产失败');
    }
}
