<?php
//用户类缓存处理
namespace app\common\service;
use app\common\model\UserAddress;
use think\Cache;
class UserService extends BaseService {
    protected $prefix = "user";

    //获取用户缓存
    public function getUserAddressListCache($user_id=null,$offset=0,$limit=10){
        $user_id = intval($user_id);
        if($user_id <= 0){
            return array();
        }
        $cache = md5($this->prefix.'getUserAddressListCache'.$user_id.$offset.$limit);
        if(Cache::store('redis')->has($cache)){
            return Cache::store('redis')->get($cache);
        }
        $userAddressModel = new UserAddress();
        $list = $userAddressModel ->getUserAddressList($user_id,$offset,$limit);
        Cache::store('redis')->set($cache,$list,$this->time);
        return $list;
    }

    //清楚用户缓存
    public function clearAddressCache($user_id=null,$offset=0,$limit=10){
        $cache = md5($this->prefix.'getUserAddressListCache'.$user_id.$offset.$limit);
        Cache::store('redis')->rm($cache);
    }
}