<?php
namespace app\common\model;
class NavGoods extends Base{
    // 表名
    protected $name = 'nav_list_goods';
    public function getNavGoodsidByCateid($cate_id){
        $navList = $this->where(array('cate_id'=>$cate_id))->field('goods_id')->select();
        return empty($navList)?array():$navList->toArray();
    }
}