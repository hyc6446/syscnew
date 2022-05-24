<?php

namespace addons\shopro\controller;


class Settled extends Base
{

    protected $noNeedLogin = ['*'];
    protected $noNeedRight = ['*'];


    public function add() {
        $params = $this->request->post();
        $this->shoproValidate($params, get_class(), 'add');
        $this->success('提交成功,请耐心等待客服人员联系', \addons\shopro\model\Settled::add($params));
    }

}
