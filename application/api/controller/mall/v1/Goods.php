<?php
namespace app\api\controller\mall\v1;

use app\api\controller\mall\Base;
use app\common\model\Goods as GoodsModel;
use app\common\model\GoodsExt;
use app\common\model\NavGoods;
use app\common\model\GoodsImage;
use app\common\service\CommonService;
use app\common\service\GoodsAttrService;
use app\common\service\GoodsService;
use app\common\service\LiveService;
use app\common\service\SpecService;
use library\XunSearch;

class Goods extends Base{

    //首页导航(废除)
    public function homeNav(){
        $commonService = new CommonService();
        $nav_list = $commonService->getNav();
        if(!empty($nav_list)){
            foreach ($nav_list as &$value){
                $value['cate_id'] = $value['id'];
                $value['cate_name'] = $value['name'];
            }
        }
        $this->_response['data']['list'] = $nav_list;

        $this->_success('v1.Goods:homeNav');
    }

    //首页商品列表（废除）
    public function homeList(){
        $cate_id = $this->_param('cate_id');
        $page = $this->_param('page');
        $pagesize = $this->_param('pagesize');
        $offset = empty($page)?$page*$pagesize:($page-1)*$pagesize;
        $limit = $pagesize;
        $navGoodsModel = new NavGoods();
        $navGoodsList = $navGoodsModel -> getNavGoodsidByCateid($cate_id);
        $goods_ids = array();
        if(!empty($navGoodsList)){
            foreach ($navGoodsList as $value){
                $goods_ids[] = $value['goods_id'];
            }
        }
        $where = array();
        $where['a.is_on_sale'] = 1;
        $where['a.check'] = 1;
        $where['a.goods_id'] = array('in',$goods_ids);
        $orderby = "a.sales_sum desc,a.sort desc";

        $goodsModel = new GoodsModel();
        $goodsService = new GoodsService();
        $liveService = new LiveService();

        $count = $goodsModel->getGoodsListByWhereCount($where);
        $hasnext = $count>$page*$pagesize?1:0;
        $goodsList = $goodsService->getGoodsListByWhereCache($where,$offset,$limit,$orderby);
        $goods_ids = array_column($goodsList,'goods_id');
        $liveGoods = $liveService->getLiveGoodsListByGoodsIdsCache($goods_ids);
        $liveGoods = arrayByField($liveGoods,'goods_id');
        if(!empty($goodsList)){
            foreach ($goodsList as &$value){
                $value['label'] = empty($value['label'])?array():json_decode($value['label'],true);
                $value['is_live_goods'] = empty($liveGoods[$value['goods_id']])?0:1;
            }
        }

        $this->_response['data']['count'] = $count;
        $this->_response['data']['has_next'] = $hasnext;
        $this->_response['data']['list'] = $goodsList;
        $this->_success('v1.Goods:homeList');
    }

    //商品详情页
    public function goodsDetail(){
        $goods_id =  $this->_param('goods_id');

        $goodsService = new GoodsService();
        $specService =  new SpecService();
        $liveService = new LiveService();
        $goodsExtModel = new GoodsExt();
        $goods = $goodsService->getGoodsDetailGoodsIdCache($goods_id);

        if(empty($goods)){
            $this->_error('DATA_NOT_EXIST');
        }
        //查询图片
        $imageList = $goodsService->getGoodsImageListByGoodsIdCache($goods_id);
        $images = array();
        if(!empty($imageList)){
            foreach ($imageList as $value){
                $images[] = $value['src'];
            }
        }
        //查询商品存在的sku
        $keys = $specService->getGoodsSkuKeyCache($goods_id);
        $key_arr = array();
        if(!empty($keys)){
            foreach ($keys as $value){
               if($value['store_count']>0){
                    foreach (explode('_',$value['key']) as $v){
                        $key_arr[] = $v;
                    }
                }
            }
        }
        $spec = $specService->getGoodsSpecByTypeidCache($goods['goods_type']);
        $spec_ids = array_column($spec,'id');
        //查询规格
        $spec = $specService -> getGoodsSpecItemListCache(array_unique($key_arr));
        $specArray = array();
        $specList = array();
        if(!empty($spec)){
            foreach ($spec as $key=>$value){
                $t = array();
                if(in_array($value['spec_value_id'],array_unique($key_arr))){
                    $t['is_able'] = 1;
                }else{
                    $t['is_able'] = 0;
                }
                $t['spec_value_id'] = $value['spec_value_id'];
                $t['spec_value_text'] = $value['item'];
                $specArray[$value['id']]['spec_name'] = $value['name'];
                $specArray[$value['id']]['spec_value'][] = $t;
            }
             foreach ($specArray as $value){
                 $specList[] = $value;
             }
        }

        $liveInfo = $liveService->getLiveGoodsInfoByGoodsIdCache($goods_id);

        $list = array();
        $list['goods_id'] = $goods['goods_id'];
        $list['goods_name'] = $goods['goods_name'];
        $list['goods_remark'] = $goods['goods_remark'];
        $list['label'] = json_decode($goods['label'],true);
        $list['market_price'] = $goods['market_price'];
        $list['shop_price'] = $goods['shop_price'];
        $list['is_free_shipping'] = $goods['is_free_shipping'];
        $list['template_id'] = $goods['template_id'];
        $list['shipping_price'] = template_freight($goods['is_free_shipping'],$goods['template_id']);
        $list['goods_content'] = $goods['goods_content'];
        $list['sales_sum'] = $goods['sales_sum'];
        $list['store_count'] = $goods['store_count'];
        $list['image'] = $images;
        $list['spec'] = $specList;
        $list['live_id'] = empty($liveInfo['live_id'])?0:$liveInfo['live_id'];
        $list['live_img'] = empty($liveInfo['img_url'])?'':$liveInfo['img_url'];

        //当前登录用户是否关注
        $list['is_collect'] = $goodsService->IsUserGoodsCollect($goods['goods_id'], $this->userInfo['id']);

        //添加浏览数
        $goodsExtModel->updateGoodsViewCount($goods_id);

        //增加点击数
        $goodsExtModel->updateGoodsClickCount($goods_id);

        $this->_response['data']['list'] = $list;

        $this->_success('v1.Goods:goodsDetail');
    }

    //商品参数
    public function goodsAttrDetail(){
        $goods_id =  $this->_param('goods_id');
        $goodsAttrService = new GoodsAttrService();
        $goodsService = new GoodsService();
        $goods = $goodsService->getGoodsDetailGoodsIdCache($goods_id);
        $attrList = $goodsAttrService->getGoodsAttrItemByTypeIdCache($goods['goods_type']);
        $attr_ids = array_column($attrList,'attr_id');
        $attr = $goodsAttrService->getGoodsAttrItemListCache($goods_id,$attr_ids);
        $this->_response['data']['list'] = $attr;
        $this->_success('v1.Goods:goodsAttrDetail');
    }

    //商品sku
    public function goodsSkuDetail(){
        $goods_id =  $this->_param('goods_id');
        $key =  $this->_param('key');
        $specService =  new SpecService();
        $sku = $specService->getGoodsSkuItemCache($goods_id,$key);
        if(!empty($sku)){
            $goodsImageModel = new GoodsImage();
            $sku['spec_img'] = $goodsImageModel->getDetail(array('goods_id'=>$goods_id,'is_seleted'=>1))['src'];
        }
        $this->_response['data']['list'] = $sku;
        $this->_success('v1.Goods:goodsSkuDetail');
    }

    //搜索
    public function goodsKeyword(){
        $keywords = $this->_param('keyword');
        $xunSearch = new XunSearch();
        $list = array();
        $goods_ids = $xunSearch ->searchGoods($keywords);
        if(!empty($goods_ids) && is_array($goods_ids)){
            $goodsModel = new GoodsModel();
            $where = array();
            $where['a.goods_id'] = array('in',$goods_ids);
            $where['a.is_on_sale'] = 1;
            $where['a.check'] = 1;
            $orderby = "a.goods_id desc,a.sort desc";
            $list = $goodsModel->getGoodsListByCondition($where,$orderby);
        }
        $this->_response['data']['list'] = $list;
        $this->_success('v1.Goods:goodsKeyword');
    }
}