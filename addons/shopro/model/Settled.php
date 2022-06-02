<?php

namespace addons\shopro\model;

use think\Model;



class Settled extends Model
{

    // 表名,不含前缀
    protected $name = 'shopro_user_settled';
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $deleteTime = 'deletetime';

    protected $hidden = ['deletetime'];


    // 追加属性
    protected $append = [
        'status_name'
    ];


    public static function add($params)
    {
        $user = User::info();
        
        extract($params);

        $self = self::create([
            "user_id" => $user->id,
            "contact" => $contact,
            "mobile" => $mobile,
            "company" => $company??'',
            "content" => $content,
            'status' => 0
        ]);

        return $self;
    }

    public function getStatusNameAttr($value, $data) {
        return $data['status'] == 1 ? '已处理' : '未处理';
    }
}
