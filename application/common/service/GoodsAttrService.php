<?php
//产品参数缓存
namespace app\common\service;
use app\common\model\GoodsAttr;
use app\common\model\GoodsAttribute;
use think\Cache;
class GoodsAttrService extends BaseService {
    protected $prefix = "goods_attr";

    //获取产品参数
    public function getGoodsAttrItemListCache($goods_id=null,$attr_ids){
        $goods_id = intval($goods_id);
        if($goods_id <= 0 ||empty($attr_ids)){
            return array();
        }
        $cache = md5($this->prefix.'getGoodsAttrItemListCache'.$goods_id.serialize($attr_ids));
        if(Cache::store('redis')->has($cache)){
            return Cache::store('redis')->get($cache);
        }
        $goodsAttrModel = new GoodsAttr();
        $list = $goodsAttrModel ->getGoodsAttrItemList($goods_id,$attr_ids);
        Cache::store('redis')->set($cache,$list,60);
        return $list;
    }

    //获取商品产品参数值
    public function getGoodsAttrItemByTypeIdCache($type_id=null){
        $type_id = intval($type_id);
        if($type_id <= 0 ){
            return array();
        }
        $where = array('type_id'=>$type_id);
        $cache = md5($this->prefix.'getGoodsAttrItemByTypeIdCache'.serialize($where));
        if(Cache::store('redis')->has($cache)){
            return Cache::store('redis')->get($cache);
        }
        $goodsAttributeModel = new GoodsAttribute();
        $list = $goodsAttributeModel ->fetchList($where,0,30);
        Cache::store('redis')->set($cache,$list,$this->time);
        return $list;
    }
}
