<?php
//商品规格缓存类
namespace  app\common\service;
use app\common\model\Spec;
use app\common\model\SpecGoodsPrice;
use app\common\model\SpecItem;
use think\Cache;
class SpecService extends BaseService {
    protected $prefix = "goods_spec";

    //获取商品规格
    public function getGoodsSpecItemListCache($item_ids){
        if(empty($item_ids)){
            return array();
        }
        $cache = md5($this->prefix.'getGoodsSpecListCache'.serialize($item_ids));
        if(Cache::has($cache)){
            return Cache::get($cache);
        }
        $specItemModel = new SpecItem();
        $list = $specItemModel ->getGoodsSpecItemList($item_ids);
        Cache::set($cache,$list,$this->time);
        return $list;
    }

    //获取商品规格和商品规格值
    public function getGoodsSpecItemByIdCache($ids=null){
        $ids= array_filter($ids,'intval');
        if(empty($ids)){
            return array();
        }
        $cache = md5($this->prefix.'getGoodsSpecItemByIdCache'.serialize($ids));
        if(Cache::has($cache)){
            return Cache::get($cache);
        }
        $specItemModel = new SpecItem();
        $list = $specItemModel ->getGoodsSpecItemById($ids);
        Cache::set($cache,$list,$this->time);
        return $list;
    }

    //获取所有商品sku
    public function getGoodsSkuItemCache($goods_id=null,$key=null){
        $goods_id = intval($goods_id);
        if($goods_id <= 0 || empty($key)){
            return array();
        }
        $cache = md5($this->prefix.'getGoodsSkuItemListCache'.$goods_id.$key);
        if(Cache::store('redis')->has($cache)){
            return Cache::store('redis')->get($cache);
        }
        $specGoodsPriceModel = new SpecGoodsPrice();
        $list = $specGoodsPriceModel ->getGoodsSkuItem($goods_id,$key);
        Cache::store('redis')->set($cache,$list,$this->time);
        return $list;
    }

    //获取所有商品sku key
    public function getGoodsSkuKeyCache($goods_id=null){
        $goods_id = intval($goods_id);
        if($goods_id <= 0 ){
            return array();
        }
        $cache = md5($this->prefix.'getGoodsSkuKeyCache'.$goods_id);
        if(Cache::store('redis')->has($cache)){
            return Cache::store('redis')->get($cache);
        }
        $specGoodsPriceModel = new SpecGoodsPrice();
        $list = $specGoodsPriceModel ->getGoodsSkuKey($goods_id);
        Cache::store('redis')->set($cache,$list,$this->time);
        return $list;
    }

    //获取所有商品sku keys
    public function getGoodsSkuKeysCache($goods_id=null,$key=null){
        $goods_id = intval($goods_id);
        if($goods_id <= 0 || empty($key)){
            return array();
        }
        $cache = md5($this->prefix.'getGoodsSkuKeysCache'.$goods_id.$key);
        if(Cache::store('redis')->has($cache)){
            return Cache::store('redis')->get($cache);
        }
        $specGoodsPriceModel = new SpecGoodsPrice();
        $list = $specGoodsPriceModel ->getGoodsSkuKeys($goods_id,$key);
        Cache::store('redis')->set($cache,$list,$this->time);
        return $list;
    }

    //获取规格
    public function getGoodsSpecByTypeidCache($type_id=null){
        $type_id = intval($type_id);
        if($type_id <= 0 ){
            return array();
        }
        $cache = md5($this->prefix.'getGoodsSpecByTypeidCache'.$type_id);
        if(Cache::store('redis')->has($cache)){
            return Cache::store('redis')->get($cache);
        }
        $specModel = new Spec();
        $list = $specModel ->fetchList(array('type_id'=>$type_id),0,10);
        Cache::store('redis')->set($cache,$list,$this->time);
        return $list;
    }

}
