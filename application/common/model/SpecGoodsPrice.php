<?php
namespace app\common\model;
class SpecGoodsPrice extends Base
{
    // 表名
    protected $name = 'spec_goods_price';

    public function fetchList($where,$field="*"){
        $list =  $this->alias('a')->field($field)->join('cc_goods b','a.goods_id=b.goods_id','left')->where($where)->select();
        return empty($list)?array():$list->toArray();
    }

    //获取所有商品规格值价格和库存
    public function getSpecGoodsPriceInfoByGoodsId($goods_id){
        $goods_id = intval($goods_id);
        if($goods_id<=0){
            return array();
        }
        $where = array();
        $where['goods_id'] = $goods_id;
        $data = $this->where($where)->select();
        return empty($data)?array():$data->toArray();
    }

    //获取商品sku
    public function getGoodsSkuItem($goods_id,$key=null){
        $goods_id = intval($goods_id);
        if($goods_id <= 0 || empty($key)){
            return array();
        }
        $where = array();
        $where['goods_id'] = $goods_id;
        $where['key'] = $key;
        $attr = $this->where($where)->find();
        return empty($attr)?array():$attr->toArray();
    }

    //获取商品sku
    public function getGoodsSkuItemByItemId($goods_id=null,$item_id=null){
        $goods_id = intval($goods_id);
        if($goods_id <= 0 || empty($item_id)){
            return array();
        }
        $where = array();
        $where['goods_id'] = $goods_id;
        $where['item_id'] = $item_id;
        $attr = $this->where($where)->find();
        return empty($attr)?array():$attr->toArray();
    }

    //获取商品sku
    public function getGoodsSkuKey($goods_id){
        $goods_id = intval($goods_id);
        if($goods_id <= 0 ){
            return array();
        }
        $where = array();
        $where['goods_id'] = $goods_id;
        $where['store_count'] = array('>',0);
        $attr = $this->where($where)->field('key,store_count')->select();
        return empty($attr)?array():$attr->toArray();
    }

    //获取商品sku
    public function getGoodsSkuKeys($goods_id,$key){
        $goods_id = intval($goods_id);
        if($goods_id <= 0 ){
            return array();
        }
        $where = array();
        $where['goods_id'] = $goods_id;
        $where['key'] = array("like","%".$key."%");
        $attr = $this->where($where)->field('key,store_count')->select();
        return empty($attr)?array():$attr->toArray();
    }
}