<?php
//商品产品参数
namespace app\common\model;
class GoodsAttr extends Base
{
    // 表名
    protected $name = 'goods_attr';

    //商品产品参数
    public function getGoodsAttrItemList($goods_id,$attr_ids){
        $goods_id = intval($goods_id);
        if($goods_id <= 0 || empty($attr_ids)){
            return array();
        }
        $where = array();
        $where['a.goods_id'] = $goods_id;
        $where['a.attr_id'] = array('in',$attr_ids);
        $attr = $this
            ->alias('a')
            ->field('b.attr_name,a.attr_value')
            ->where($where)
            ->join('cc_goods_attribute b','a.attr_id=b.attr_id','left')
            ->order('b.order asc')
            ->select();
        return empty($attr)?array():$attr->toArray();
    }

}