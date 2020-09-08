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
define('IMAGE_PATH', __DIR__ . '/../public/');

//调整配置文件的路径
// define('CONF_PATH',  __DIR__.'/../config/');

// 加载框架引导文件
require __DIR__ . '/../framework/base.php';

define('BIND_MODULE','admin');


// 设置根url
\think\Url::root('');

// 执行应用
\think\App::run()->send();
