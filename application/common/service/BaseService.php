<?php
namespace app\common\service;
use think\Cache;

class BaseService{

    public $redisCache = null;//实例
    public $time = 1;//缓存时间

    public function __construct()
    {
        $this->redisCache = Cache::store('redis')->handler();
    }
}