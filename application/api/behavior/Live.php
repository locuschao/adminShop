<?php
// +----------------------------------------------------------------------
// | 直播间的事件
// +----------------------------------------------------------------------
// | Copyright (c) 2019-2020 rights reserved.
// +----------------------------------------------------------------------
// | Author: wuyh
// +----------------------------------------------------------------------
// | Date: 2020/3/30 15:03
// +----------------------------------------------------------------------
namespace app\api\behavior;

use app\common\service\LiveService;
use library\CacheKey;
use think\Cache;
use think\controller;

class Live extends controller
{
    protected $redis = null;

    protected $appId = 1;

    public function _initialize()
    {
        $redisConf = ['type' => 'Redis', 'redis_host' => 'live'];
        $this->redis = Cache::connect($redisConf);
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

        $data = isset($params['data']) && $params['data'] ? $params['data'] : [];
        $res = $this->$method($data);
        if ($res) $params['code'] = 1;
    }

    /**
     * 直播间支付下单通知
     * @param $params
     * @return bool
     * @Author: wuyh
     * @Date: 2020/3/31 17:25
     */
    public function payNotify(&$params)
    {
        return true;

    }
}