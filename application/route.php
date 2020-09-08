<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
use think\Route;

//modify wuyh 2020-03-04

return [
    '__domain__' => [
        // 'api.mall' => 'api', //本地
        'test.weyu.api' => 'api',
    ],

    // #商场接口路由
    //  '[api]' =>[
    //      // ":ver.:controler/:function" => 'api/mall/:ver.controler/:function',
    //      ":controller:/:function" => "index",
    //  ],

    // //直播路由
    // '[live]'     => [
    //     ":ver.:controler/:function" => "api/live.:ver.:controler/:function"
    // ],
];
