<?php
namespace app\common\service;
use app\common\model\Ad;
use think\Cache;
class AdService extends BaseService {

    protected $prefix = "ad";

    //首页banner
    public function getHomeBannerListCache($pos_id=null){
        $pos_id = intval($pos_id);
        if($pos_id <= 0){
            return array();
        }
        $cache = md5($this->prefix.'getHomeBannerListCache'.$pos_id);

        if(Cache::store('redis')->has($cache)){
            return Cache::store('redis')->get($cache);
        }
        $adModel = new Ad();
        $list = $adModel ->getAdListByPosid($pos_id);
        Cache::store('redis')->set($cache,$list,$this->time);
        return $list;
    }
}