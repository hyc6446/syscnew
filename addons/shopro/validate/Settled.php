<?php

namespace addons\shopro\validate;

use think\Validate;

class Settled extends Validate
{

    /**
     * 验证规则
     */
    protected $rule = [
        'contact' => 'require',
        'content' => 'require',
        'mobile' => 'regex:^1\d{10}$'
    ];

    /**
     * 提示消息
     */
    protected $message = [
        'contact.require' => '联系人必须填写',
        'content.require' => '内容说明必须填写',
        'mobile.regex' => '联系电话格式不正确',
    ];

    /**
     * 字段描述
     */
    protected $field = [
        
    ];

    /**
     * 验证场景
     */
    protected $scene = [
        'add' => ['contact', 'content','mobile']
    ];

}
