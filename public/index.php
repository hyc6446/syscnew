<?php

// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
// [ 应用入口文件 ]
// 定义应用目录
define('APP_PATH', __DIR__ . '/../application/');

// 判断是否安装
if (!is_file(APP_PATH . 'admin/command/Install/install.lock')) {
    header("location:./install.php");
    exit;
}

// var_dump($_SERVER);die;
// 执行允许跨域处理
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: *");
header('Access-Control-Allow-Credentials:true');
header('Access-Control-Allow-Headers:content-type,platform,token');

//# 允许跨域请求的域，*代表所有
//add_header 'Access-Control-Allow-Origin' '*';
//        # 允许带上cookie请求
//        add_header 'Access-Control-Allow-Credentials' 'true';
//        # 允许请求的方法，比如 GET/POST/PUT/DELETE
//        add_header 'Access-Control-Allow-Method' '*';
//        # 允许请求的header
//        add_header 'Access-Control-Allow-Headers' 'content-type,platform,token';



// 加载框架引导文件
require __DIR__ . '/../thinkphp/start.php';
