<?php

namespace app\admin\controller\shopro\box;

use app\common\controller\Backend;
use think\Db;
use think\exception\PDOException;

/**
 * 盲盒管理
 *
 * @icon fa fa-circle-o
 */
class Box extends Backend
{

    /**
     * Box模型对象
     * @var \app\admin\model\shopro\box\Box
     */
    protected $model = null;
    protected $modelValidate = true;
    protected $multiFields = "switch";
    protected $noNeedRight = ['selectpage'];

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\shopro\box\Box;

    }



    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */
    public function index()
    {
        //设置过滤方法
        $this->request->filter(['strip_tags', 'trim']);
        if ($this->request->isAjax()) {
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField')) {
                return $this->selectpage();
            }
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();

            $list = $this->model->alias('box')
                ->field('box.box_name,box.id,box.box_banner_images,box.coin_price,box.start_time,box.end_time,box.update_time,box.is_hot,box.is_cheap,box.is_try,box.sort,box.switch,box.detail_id')
                ->field('category.name category_name')
                ->join('category category', 'category.id = box.category_id', 'left')
                ->where($where)
                ->order($sort, $order)
                ->paginate($limit);

            $result = array("total" => $list->total(), "rows" => $list->items());

            return json($result);
        }
        return $this->view->fetch();
    }

    public function selectpage()
    {
        return parent::selectpage();
    }

    public function multi($ids = "")
    {
        if (!$this->request->isPost()) {
            $this->error(__("Invalid parameters"));
        }
        $ids = $ids ? $ids : $this->request->post("ids");
        if ($ids) {
            if ($this->request->has('params')) {
                parse_str($this->request->post("params"), $values);
                $values = $this->auth->isSuperAdmin() ? $values : array_intersect_key($values, array_flip(is_array($this->multiFields) ? $this->multiFields : explode(',', $this->multiFields)));
                if ($values) {
                    $adminIds = $this->getDataLimitAdminIds();
                    if (is_array($adminIds)) {
                        $this->model->where($this->dataLimitField, 'in', $adminIds);
                    }
                    $count = 0;
                    Db::startTrans();
                    try {
                        if (isset($values['is_try'])) {
                            if (strpos($ids, ',')) {
                                throw new \Exception('只能设置一个盲盒为试一试');
                            }

                            // 所有盲盒取消试一试
                            \app\admin\model\shopro\box\Box::update(['is_try' => 0], ['is_try' => 1]);

                            $list = $this->model->where($this->model->getPk(), 'in', $ids)->select();
                            foreach ($list as $index => $item) {
                                $count += $item->allowField(true)->isUpdate(true)->save($values);
                            }
                        }
                        if (isset($values['switch'])) {

                            \app\admin\model\shopro\box\Box::where(['id'=>$ids])->update($values);
                            $count++;
                        }

                        Db::commit();
                    } catch (PDOException $e) {
                        Db::rollback();
                        $this->error($e->getMessage());
                    } catch (\Exception $e) {
                        Db::rollback();
                        $this->error($e->getMessage());
                    }
                    if ($count) {
                        $this->success();
                    } else {
                        $this->error(__('No rows were updated'));
                    }
                } else {
                    $this->error(__('You have no permission'));
                }
            }
        }
        $this->error(__('Parameter %s can not be empty', 'ids'));
    }

    public function import()
    {
        parent::import();
    }


}
