<?php

namespace addons\shopro\controller;


class PriorityBuy extends Base
{

    protected $noNeedLogin = ['*'];
    protected $noNeedRight = ['*'];


    // faq 列表
    public function list()
    {
        $params = $this->request->get();
        $uid = $this->auth->id;
        $this->success('获取成功', \addons\shopro\model\PriorityBuy::buyerList($uid));
    }


    // public function detail () {
    //     $id = $this->request->get('id');

    //     $this->success('签到成功', \addons\shopro\model\Faq::where('id', $id)->find());
    // }

}
