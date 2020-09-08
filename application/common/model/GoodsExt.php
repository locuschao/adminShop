<?php
namespace app\common\model;
class GoodsExt extends Base{
    // 表名
    protected $name = 'goods_ext';

    //更新商品浏览数
    public function updateGoodsViewCount($goods_id){
        $goods_id = intval($goods_id);
        $goods = $this->where(array('goods_id'=>$goods_id))->find();
        if(empty($goods)){
            //插入
            $this->insert(array('goods_id'=>$goods_id,'view_count'=>1));
        }else{
            //更新
            $this->where(array('goods_id'=>$goods_id))->setInc('view_count',1);
        }

    }

    //更新商品点击数
    public function updateGoodsClickCount($goods_id){
        $goods = $this->where(array('goods_id'=>$goods_id))->find();
        if(empty($goods)){
            //插入
            $this->insert(array('goods_id'=>$goods_id,'click_count'=>1));
        }else{
            //更新
            $this->where(array('goods_id'=>$goods_id))->setInc('click_count',1);
        }
    }

    //更新商品收藏数
    public function updateGoodsCollectCount($goods_id){
        $goods = $this->where(array('goods_id'=>$goods_id))->find();
        if(empty($goods)){
            //插入
            $this->insert(array('goods_id'=>$goods_id,'collect_count'=>1));
        }else{
            //更新
            $this->where(array('goods_id'=>$goods_id))->setInc('collect_count',1);
        }
    }

    //更新商品销量
    public function updateGoodsRealSalesCount($goods_id,$num){
        $goods = $this->where(array('goods_id'=>$goods_id))->find();
        if(empty($goods)){
            //插入
            $this->insert(array('goods_id'=>$goods_id,'real_sales_count'=>$num));
        }else{
            //更新
            $this->where(array('goods_id'=>$goods_id))->setInc('real_sales_count',$num);
        }
    }
}