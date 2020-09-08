<?php
/**
 * author:pc
 * desc:xunsearch使用
 */
namespace library;
class XunSearch{
    public function searchGoods($keyword=null){
        $data = array();
        if(empty($keyword)){
            return $data;
        }
        $xs = new \XS(EXTEND_PATH."xunSearch/goods.ini");
        $xs->search->addWeight('goods_name', $keyword);
        $xs->search->addWeight('keywords', $keyword);
        $xs->search->addWeight('goods_remark', $keyword);
        $docs = $xs->search->search($keyword);
        if(empty($docs)){
            return $data;
        }
        foreach($docs as $doc){
            $data[] = $doc->goods_id;
        }
        return $data;
    }

    //添加商品索引
    public function addGoodsIndex($data){
        $xs = new \XS(EXTEND_PATH."xunSearch/goods.ini");
        $index = $xs->index;
        $doc = new \XSDocument;
        $doc->setFields($data);
        $index->add($doc);
    }

    //更新商品索引
    public function saveGoodsIndex($data){
        $xs = new \XS(EXTEND_PATH."xunSearch/goods.ini");
        $index = $xs->index;
        $doc = new \XSDocument;
        $doc->setFields($data);
        $index->update($doc);
    }

    public function searchMenu($keyword=null){
        $data = array();
        if(empty($keyword)){
            return $data;
        }
        $xs = new \XS(EXTEND_PATH."xunSearch/menu.ini");
        $docs = $xs->search->search($keyword);
        if(empty($docs)){
            return $data;
        }
        foreach($docs as $doc){
            $data[] = $doc->id;
        }
        return $data;
    }

    //添加菜单索引
    public function addMenuIndex($data){
        $xs = new \XS(EXTEND_PATH."xunSearch/menu.ini");
        $index = $xs->index;
        $doc = new \XSDocument;
        $doc->setFields($data);
        $index->add($doc);
    }

    //更新菜单索引
    public function saveMenuIndex($data){
        $xs = new \XS(EXTEND_PATH."xunSearch/menu.ini");
        $index = $xs->index;
        $doc = new \XSDocument;
        $doc->setFields($data);
        $index->update($doc);
    }

    /**
     * [搜索优惠券]
     */
    public function searchCoupon($keyword=null){
        $data = array();
        if(empty($keyword)){
            return $data;
        }
        $xs = new \XS(EXTEND_PATH."xunSearch/coupon.ini");
        $xs->search->addWeight('title', $keyword);
        $docs = $xs->search->search($keyword);
        if(empty($docs)){
            return $data;
        }
        foreach($docs as $doc){
            $data[] = $doc->id;
        }
        return $data;
    }

    /**
     * @param null $keyword
     * @return array
     * [查询商品优惠活动]
     */
    public function searchGoodsActivity($keyword=null){
        $data = array();
        if(empty($keyword)){
            return $data;
        }
        $xs = new \XS(EXTEND_PATH."xunSearch/goods_activity.ini");
        $xs->search->addWeight('title', $keyword);
        $docs = $xs->search->search($keyword);
        if(empty($docs)){
            return $data;
        }
        foreach($docs as $doc){
            $data[] = $doc->id;
        }
        return $data;
    }


    //查询商家
    public function searchMerchants($keyword=null){
        $data = array();
        if(empty($keyword)){
            return $data;
        }
        $xs = new \XS(EXTEND_PATH."xunSearch/merchants.ini");
        $xs->search->addWeight('platform', $keyword);
        $docs = $xs->search->search($keyword);
        if(empty($docs)){
            return $data;
        }
        foreach($docs as $doc){
            $data[] = $doc->id;
        }
        return $data;
    }

    //查询礼包活动
    public function searchActivityGift($keyword=null){
        $data = array();
        if(empty($keyword)){
            return $data;
        }
        $xs = new \XS(EXTEND_PATH."xunSearch/gift_activity.ini");
        $xs->search->addWeight('title', $keyword);
        $docs = $xs->search->search($keyword);
        if(empty($docs)){
            return $data;
        }
        foreach($docs as $doc){
            $data[] = $doc->id;
        }
        return $data;
    }


}