<?php

namespace app\admin\controller\shopro\user;

use addons\shopro\model\Goods;
use app\common\controller\Backend;
use think\Db;
use think\exception\PDOException;
use think\exception\ValidateException;

/**
 * 
 *
 * @icon fa fa-circle-o
 */
class Collect extends Backend
{

    /**
     * Collect模型对象
     * @var \app\admin\model\shopro\user\Collect
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\shopro\user\Collect;

    }



    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */


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
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            $total = $this->model
                ->where($where)
                ->order($sort, $order)
                ->count();

            $list = $this->model
                ->with(['user' => function ($query) {
                    return $query->withField('id, nickname, avatar');
                },'give_user' => function ($query) {
                return $query->withField('id, nickname, avatar');
            },'goods'=>function($query){
                    return $query->withField('id, title, image');
                }])
                ->where($where)
                ->order($sort, $order)
                ->limit($offset, $limit)
                ->select();

            $list = collection($list)->toArray();
            $result = array("total" => $total, "rows" => $list);

            $this->success('', null, $result);
        }
        return $this->view->fetch();
    }


    /**
     * 编辑
     */
    public function edit($id = null)
    {
        $row = $this->model->get($id);
        if (!$row) {
            $this->error(__('No Results were found'));
        }
        if ($this->request->isPost()) {
            $params = $this->request->post();
            if ($params) {
                $params = json_decode($params['data'], true);
                $result = false;
                Db::startTrans();
                try {
                    $result = $row->allowField(true)->save($params);
                    Db::commit();
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
                    $this->success();
                } else {
                    $this->error(__('No rows were updated'));
                }
            }
            $this->error(__('Parameter %s can not be empty', ''));
        }
        $row->user = \app\admin\model\shopro\user\User::where('id', $row->user_id)->field('id, nickname, avatar')->find();
        $this->assignconfig("row", $row);
        return $this->view->fetch();
    }

    /**
     * 回收站
     */
    public function recyclebin()
    {
        //设置过滤方法
        $this->request->filter(['strip_tags']);
        if ($this->request->isAjax()) {
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            $total = $this->model
                ->onlyTrashed()
                ->where($where)
                ->order($sort, $order)
                ->count();

            $list = $this->model
                ->onlyTrashed()
                ->where($where)
                ->order($sort, $order)
                ->limit($offset, $limit)
                ->select();

            $result = array("total" => $total, "rows" => $list);

            return json($result);
        }
        return $this->view->fetch();
    }

    public function grantShard()
    {
        $ids = $this->request->post('ids','');
        $uid = $this->request->post('uid','');
        if (!$ids)$this->error('请选择藏品');
        if (!$uid)$this->error('请选择空投的用户');
        $user = \app\admin\model\User::get($uid);
        if (!$user)$this->error('用户不存在,请确认后再输入');
        if (!$user['addr'])$this->error('该用户未登录小程序注册数字资产账户');
        $ids = explode(',',$ids);
        foreach ($ids as $value){
            //空投 todo:上链
            $res =\addons\shopro\model\UserCollect::edit([
                'user_id'=>$user['id'],
                'goods_id'=>$value,
                'type'=>5,
                'status'=>0,
            ]);
            if (!$res){
                $this->error('空投失败');
            }
        }
        $this->success('空投成功');
    }

}
