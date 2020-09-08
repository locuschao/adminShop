<?php
// +----------------------------------------------------------------------
// | WEBSOCET 服务设置
// +----------------------------------------------------------------------
// | Copyright (c) 2019-2020 rights reserved.
// +----------------------------------------------------------------------
// | Author: wuyh
// +----------------------------------------------------------------------
// | Date: 2020/3/18 15:23
// +----------------------------------------------------------------------

return [
    'host' => '0.0.0.0',
    'port' => 9522,
    'mode' => '', // 运行模式 默认为SWOOLE_PROCESS
    'sock_type' => '', //默认为SWOOLE_SOCK_TCP

    // 可以支持swoole的所有配置参数
    'pid_file' => RUNTIME_PATH . 'swoole.pid',
    'log_file' => RUNTIME_PATH . 'swoole.log',
    'task_worker_num' => 4,
    'daemonize' => false,//守护
    'worker_num' => 4,
    'max_request' => 10000,

    'master_process_name' => 'XinGoServer',

    //心跳
//    'heartbeat_check_interval' => 5,
//    'heartbeat_idle_time' => 30,

    //事件回调
    'onOpen' => [new app\swoole\service\Server(), 'onOpen'],
    'onMessage' => [new app\swoole\service\Server(), 'onMessage'],
//    'onRequest' => [new app\swoole\service\Server(), 'onRequest'],
    'onClose' => [new app\swoole\service\Server(), 'onClose'],
    'onTask' => [new app\swoole\service\Server(), 'onTask'],
    'onFinish' => [new app\swoole\service\Server(), 'onFinish'],
];