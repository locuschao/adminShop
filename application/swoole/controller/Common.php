<?php
// +----------------------------------------------------------------------
// | 随便起名 WEBSOCKET COMMON 控制器
// +----------------------------------------------------------------------
// | Copyright (c) 2019-2020 rights reserved.
// +----------------------------------------------------------------------
// | Author: wuyh
// +----------------------------------------------------------------------
// | Date: 2020/3/19 14:34
// +----------------------------------------------------------------------
namespace app\swoole\controller;

class Common extends Base
{
    public function index(){
        $params = ['code' => 0, 'msg' => 'welcome xingo mall'];
        return json_encode($params);
    }
}