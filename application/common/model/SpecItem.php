<?php
//商品规格表
namespace app\common\model;
class SpecItem extends Base
{
    // 表名
    protected $name = 'spec_item';

    //获取商品规格
    public function getGoodsSpecItemList($item_ids){
        $item_ids = array_filter($item_ids,'intval');
        if(empty($item_ids)){
            return array();
        }
        $where = array();
        $where['a.id'] = array('in',$item_ids);
        $specItem = $this->alias('a')->field('a.id as spec_value_id, a.item,b.id,b.name')->where($where)->join('cc_spec b','a.spec_id=b.id','left')->select();
        return empty($specItem)?array():$specItem->toArray();
    }

    //获取商品规格和规格值
    public function getGoodsSpecItemById($id){
        $where = array();
        $where['a.id'] = array('in',$id);
        $specItem = $this->alias('a')->field('a.id as spec_value_id, a.item,b.id,b.name')->where($where)->join('cc_spec b','a.spec_id=b.id','left')->select();
        return empty($specItem)?array():$specItem->toArray();
    }

}