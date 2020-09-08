<?php
/**
 * 商城首页
 */
namespace app\api\controller\mall\v1;
use app\api\controller\mall\Base;
use app\common\service\GoodsService;
use app\common\model\GoodsRecom;
use app\common\model\Goods as GoodsModel;
use app\common\service\LiveService;

class Home extends Base{
    //banner图
    public function banner(){
        $goodsService = new GoodsService();
        $list = $goodsService->getRecomBannerListCache(GoodsRecom::LUN_BO);
        if(!empty($list)){
            foreach ($list as $key=>$value){
               unset($list[$key]['extend_cat_id']);
               unset($list[$key]['label']);
            }
        }
        $this->_response['data']['list'] = $list;
        $this->_success('v1.home:banner');
    }

    //推荐位
    public function recom(){
        $goodsService = new GoodsService();
        $list = $goodsService->getRecomBannerListCache(GoodsRecom::TUI_JIAN);
        if(!empty($list)){
            foreach ($list as $key=>&$value){
                unset($list[$key]['extend_cat_id']);
                unset($list[$key]['label']);
                $label = array();
                if($value['is_new']){
                    array_push($label,array('color'=>' #1C85E6','text'=>'新品'));
                }
                $value['label']=$label;
            }
        }
        $this->_response['data']['list'] = $list;
        $this->_success('v1.home:recom');
    }

    //商品上新
    public function goodsList(){
        $page = (int)$this->_param('page');
        $pagesize = (int)$this->_param('pagesize');
        $offset = empty($page)?$page*$pagesize:($page-1)*$pagesize;
        $limit = $pagesize;
        $where = array();
        $where['a.is_on_sale'] = 1;
        $where['a.check'] = 1;
        $where['b.is_seleted'] = 1;
        $orderby = "a.goods_id  desc";
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
                $label = array();
                if($value['is_recommend']){
                    array_push($label,array('color'=>'#F7AA00','text'=>'推荐'));
                }
                if($value['is_new']){
                    array_push($label,array('color'=>' #1C85E6','text'=>'新品'));
                }
                $value['label']=$label;
                $value['is_live_goods'] = empty($liveGoods[$value['goods_id']])?0:1;
            }
        }
        $this->_response['data']['count'] = $count;
        $this->_response['data']['has_next'] = $hasnext;
        $this->_response['data']['list'] = $goodsList;
        $this->_success('v1.home:goodsList');
    }
}