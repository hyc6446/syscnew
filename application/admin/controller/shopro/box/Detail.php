<?php

namespace app\admin\controller\shopro\box;

use app\common\controller\Backend;
use think\Db;
use think\exception\PDOException;
use think\exception\ValidateException;

/**
 * 盲盒详情
 *
 * @icon fa fa-circle-o
 */
class Detail extends Backend
{
    protected $modelValidate = true;

    /**
     * Detail模型对象
     * @var \app\admin\model\shopro\box\Detail
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\shopro\box\Detail;

    }



    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */
    public function index($boxid = null)
    {
        $boxid = $boxid ? $this->request->get('boxid') : '';
        if (empty($boxid)) {
            $this->error('盲盒有误');
        }

        $boxid_filter = '?box_id=' . $boxid;
        $this->assignconfig('boxid_filter', $boxid_filter);

        //设置过滤方法
        $this->request->filter(['strip_tags', 'trim']);
        if ($this->request->isAjax()) {
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField')) {
                return $this->selectpage();
            }
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();

            $list = $this->model->alias('detail')
                ->field('detail.*')
                ->field('goods.title as goods_name,goods.image goods_image')
                ->field('box.box_name')
                ->join('shopro_box box', 'box.id = detail.box_id', 'left')
                ->join('shopro_goods goods', 'goods.id = detail.goods_id', 'left')
                ->where($where)
                ->where('detail.box_id', $boxid)
                ->order($sort, $order)
                ->paginate($limit);

            $result = array("total" => $list->total(), "rows" => $list->items());

            return json($result);
        }
        return $this->view->fetch();
    }

    public function add()
    {
        if ($this->request->isPost()) {
            $params = $this->request->post("row/a");
            if ($params) {
                $params = $this->preExcludeFields($params);

                if ($this->dataLimit && $this->dataLimitFieldAutoFill) {
                    $params[$this->dataLimitField] = $this->auth->id;
                }
                $result = false;
                Db::startTrans();
                try {
                    //是否采用模型验证
                    if ($this->modelValidate) {
                        $name = str_replace("\\model\\", "\\validate\\", get_class($this->model));
                        $validate = is_bool($this->modelValidate) ? ($this->modelSceneValidate ? $name . '.add' : $name) : $this->modelValidate;
                        $this->model->validateFailException(true)->validate($validate);
                    }
                    $result = $this->model->allowField(true)->save($params);
                    Db::commit();
                } catch (ValidateException $e) {
                    Db::rollback();
                    $this->error($e->getMessage());
                } catch (PDOException $e) {
                    Db::rollback();
                    $this->error($e->getMessage());
                } catch (\Exception $e) {
                    Db::rollback();
                    $this->error($e->getMessage());
                }
                if ($result !== false) {
                    $this->success();
                } else {
                    $this->error(__('No rows were inserted'));
                }
            }
            $this->error(__('Parameter %s can not be empty', ''));
        }

        $box_id = $this->request->get('box_id');
        if (empty($box_id)) {
            $this->error('盲盒有误', '');
        }

        $box_name = \app\admin\model\shopro\box\Box::where('id', $box_id)->value('box_name');
        if (!$box_name) {
            $this->error('盲盒有误', '');
        }
        $this->assign('box_info', ['box_name' => $box_name, 'box_id' => $box_id]);
        return $this->view->fetch();
    }

    public function import()
    {
        parent::import();
    }


}
