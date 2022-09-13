<?php

namespace app\admin\controller\shopro;

use app\admin\model\Admin;
use app\common\controller\Backend;
use app\admin\model\shopro\Category as CategoryModel;
use fast\Tree;
use nft\Nfts;
use think\Db;
use think\exception\PDOException;
use think\exception\ValidateException;
use Exception;
use think\Log;

/**
 * 分类管理
 */
class Category extends Backend
{
    /**
     * @var \app\admin\model\shopro\Category
     */
    protected $model = null;
    protected $categorylist = [];
    protected $noNeedRight = ['selectpage', 'gettree'];

    public function _initialize()
    {
        parent::_initialize();
        $this->model = model('app\admin\model\shopro\Category');
    }

    /**
     * 选择分类
     */
    public function select()
    {
        if ($this->request->isAjax()) {
            $list = $this->model->with('children.children.children')->where('pid', 0)->order('weigh desc, id asc')->select();
            $this->success('选择分类', null, $list);
        }
        return $this->view->fetch();
    }

    /**
     * 查看
     */
    public function index()
    {
        //设置过滤方法
        $this->request->filter(['strip_tags']);
        if ($this->request->isAjax()) {
            $children =  $this->model->where('pid','<>',0)->where(['status'=>'normal'])->select();

               foreach ($children as $data){
                   if ($data['wcl_status']!=1&&$data['wcl_status']!=2){
                       $this->model->setWclCate($data['id']);
                   }
               }
            $list = $this->model->with('children.children.children')->where('pid', 0)->order('weigh desc, id asc')->select();
            $this->success('自定义分类', null, $list);
        }
        return $this->view->fetch();
    }

    /**
     * 添加自定义分类
     */
    public function add()
    {
        if ($this->request->isPost()) {
            $params = $this->request->post();
            if ($params) {
                $params = json_decode($params['data'], true);
                $result = false;
                Db::startTrans();
                try {
                    $result = $this->model->allowField(true)->save($params);
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
                    $this->success('添加成功', null, $this->model->id);
                } else {
                    $this->error(__('No rows were inserted'));
                }
            }
            $this->error(__('Parameter %s can not be empty', ''));
        }
        return $this->view->fetch();
    }

    /**
     * 编辑
     */
    public function edit($ids = null)
    {
        $row = $this->model->get($ids);
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
                    $result = true;
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
        $this->assignconfig("row", $row);
        return $this->view->fetch();
    }

    public function update($ids = null)
    {
        $row = $this->model->get($ids);
        //查看当前链账户状态
        $admin = (new Admin())->setAdmin((int)session('admin.id'));
        if ($admin['wcl_status']!=1){
            $this->error('您的链账户还未上链成功，暂时不能操作');
        }
        if (!$row) {
            $this->error(__('No Results were found'));
        }
        $params = $this->request->post();
        
        if($params) {
            $data = json_decode($params['data'], true);
            //递归处理分类数据
            $this->createOrUpdateCategory($data, $ids);
            $this->success();
        }
    }

    private function createOrUpdateCategory($data, $pid)
    {
        
        foreach($data as $k => $v) {
            $v['pid'] = $pid;
            if(!empty($v['id'])) {
                $row = $this->model->get($v['id']);
                if($row) {
                    if(isset($v['deleted']) && $v['deleted'] == 1) {
                        $row->delete();
                    }else {
                        $row->allowField(true)->save($v);
                    }
                }
            }else{
                $category = new \app\admin\model\shopro\Category;
                $category->allowField(true)->save($v);
                $v['id'] = $category->id;
                
                //创建nft类别
                $admin = Admin::get((int)session('admin.id'));
                
                $v['owner'] = $admin['addr'];
                $v['class_id'] = (new Nfts())->getClassId($v['id']);
                // var_dump($v['class_id']);exit;
                $image = cdnurl($v['image'],true);
                $v['hash'] = hash("sha256",$image);
                
                $res = (new Nfts())->nftClasses($v);
                // var_dump($res);exit;
                if (isset($res['error'])){
                    Log::error('创建类别：：：：：'.($v['name']??'').'code:'.$res['error']['code'].'====msg:'.$res['error']['message']);
                }
                
                if (isset($res['data'])){
                    $rows = $this->model->get($v['id']);
                    $rows->allowField(true)->save([
                        'wcl_class_id'=>$v['class_id'],
                        'wcl_owner'=>$v['owner'],
                        'wcl_hash'=>$v['hash'],
                        'wcl_name'=>$v['name'],
                        'operation_id'=>$res['data']['operation_id']??''
                    ]);
                    $this->model->setWclCate($v['id']);
                }
            }
            if(!empty($v['children'])) {
                $this->createOrUpdateCategory($v['children'], $v['id']);
            }
        }
    }


    public function collect()
    {
        //设置过滤方法
        $this->request->filter(['strip_tags']);
        if ($this->request->isAjax()) {
            $list = $this->model->with('children.children.children')->where('pid', 1)->order('weigh desc, id asc')->select();
            foreach ($list as &$val){
                $val['id'] = (string)$val['id'];
            }
            $this->success('自定义分类', null, $list);
        }
        return $this->view->fetch();
    }


}
