<?php
// +----------------------------------------------------------------------
// | 直播通知
// +----------------------------------------------------------------------
// | Copyright (c) 2019-2020 rights reserved.
// +----------------------------------------------------------------------
// | Author: wuyh
// +----------------------------------------------------------------------
// | Date: 2020/3/20 19:00
// +----------------------------------------------------------------------
namespace app\api\behavior;

use think\controller;
use app\common\model\Live;

class LiveNotify extends controller
{
    public function _initialize()
    {
    }

    /**
     * @param $params
     * @return bool
     * @Author: wuyh
     * @Date: 2020/3/20 19:07
     */
    public function run(&$params)
    {
        $method = $params['method'];
        if (!method_exists($this, $method)) return false;

        $data   = isset($params['data']) && $params['data'] ? $params['data'] : [];
        return $this->$method($data);
    }

    /**
     * @param $params
     * @return bool
     * @Author: wuyh
     * @Date: 2020/3/20 19:18
     */
    public function payOrder($params)
    {
        if (empty($params)) return false;
        if (!isset($params['live_id']) || empty($params['live_id'])) return false;

        $liveInfo = Live::where(['live_id' => $params['live_id'], 'status'=> Live::LIVE_BEGIN])->find();
        if (empty($liveInfo)) return false;
        
        $data = [
            'cmd' => 'payOrder',
            'module' => 'room',
            'data' => [
                'user_id' => 1,
                'user_name' => '张三',
                'order_amount' => 100,
            ]
        ];

        $data = json_encode($params);
        $res = global_Curl(config('cfg.LIVE_WS_URL'), $data,'POST');


        return $res;

        //失败后是否入列重新通知
    }
}
