<?php
namespace app\common\service;
use app\common\model\Nav;
use app\common\model\Region;
use app\common\model\Shipping;
use think\Cache;
class CommonService extends BaseService {
    protected $prefix = "common";

    /**
     * 指定时间内，禁止重复下单
     * @return boolean
     */
    public function requestDuplicateCheck($key,$time=15){

        //redis系统的当前时间
        $redis_time = $this->redisCache->time()[0];

        //初步加锁
        $isLock = $this->redisCache->setnx($key,$redis_time+$time);

        if($isLock)
        {
            return true;
        }
        else
        {
            //加锁失败的情况下。判断锁是否已经存在，如果锁存在切已经过期，那么删除锁。进行重新加锁
            $val = $this->redisCache->get($key);

            if($val && $val<time())
            {
                $this->redisCache->del($key);
            }
            return $this->redisCache->setnx($key,$redis_time+$time);
        }


    }

    //获取所有省市区
    public function getAllProvinceCache($level){
        $level = intval($level);
        if($level <= 0){
            return array();
        }
        $cache = md5($this->prefix.'getAllProvinceCache'.$level);

        if(Cache::store('redis')->has($cache)){
            return Cache::store('redis')->get($cache);
        }
        $regionModel = new Region();
        $list = $regionModel ->getAllProvinces($level);
        Cache::store('redis')->set($cache,$list,60*60*60);
        return $list;
    }

    //获取所有地区
    public function getAllRegionCache(){
        $cache = md5($this->prefix.'getAllRegionCache2');

        if(Cache::has($cache)){
            return Cache::get($cache);
        }
        $regionModel = new Region();
        $list = $regionModel ->getAllRegion();
        Cache::set($cache,$list,60*60*60);
        return $list;
    }

    //通过pid获取地区
    public function getRegionByPidCache($pid){
        $pid = intval($pid);
        if($pid <= 0){
            return array();
        }
        $cache = md5($this->prefix.'getRegionByPidCache'.$pid);

        if(Cache::store('redis')->has($cache)){
            return Cache::store('redis')->get($cache);
        }
        $regionModel = new Region();
        $list = $regionModel ->getDetail(array('pid'=>$pid));
        Cache::store('redis')->set($cache,$list,60);
        return $list;
    }

    //获取导航
    public function getNav(){
        $cache = md5($this->prefix.'getNav');

        if(Cache::store('redis')->has($cache)){
            return Cache::store('redis')->get($cache);
        }
        $navModel = new Nav();
        $list = $navModel ->getNav();
        Cache::store('redis')->set($cache,$list,60);
        return $list;
    }

    //获取所有快递
    public function getAllexpressCache(){
        $cache = md5($this->prefix.'getAllexpressCache');

        if(Cache::store('redis')->has($cache)){
            return Cache::store('redis')->get($cache);
        }
        $shippingModel = new Shipping();
        $list = $shippingModel ->fetchList(array('is_open'=>1),0,10);
        Cache::store('redis')->set($cache,$list,60);
        return $list;
    }

}