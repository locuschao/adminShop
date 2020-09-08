<?php
namespace app\api\controller\mall\v1;
use app\api\controller\mall\Base;
use app\common\model\GoodsCategory;
use app\common\service\CategoryService;
use app\common\service\GoodsService;
use app\common\model\Goods as GoodsModel;
use app\common\service\LiveService;

class Cate extends Base{

    //分类
    public function cateList(){
        $categoryService =  new CategoryService();
        $categoryList = $categoryService ->getCategoryListCache(GoodsCategory::ONE_LEVEl);
        $list = array();
        if(!empty($categoryList)){
            foreach ($categoryList as $key=>$value){
                $list[$key]['cate_id']=$value['id'];
                $list[$key]['cate_name']=$value['name'];
                $list[$key]['image']=$value['image'];
            }
        }
        $this->_response['data']['list'] = $list;
        $this->_success('v1.Cate:cateList');
    }

    //分类导航
    public function cateListNav(){
        $cate_id = (int)$this->_param('cate_id');
        $categroyService = new CategoryService();
        $categoryList = $categroyService->getThreeCategoryNav($cate_id);
        $list = array();
        if(!empty($categoryList)){
            foreach ($categoryList as $key=>$value){
                $list[$key]['cate_id']=$value['id'];
                $list[$key]['cate_name']=$value['name'];
            }
        }

        $this->_response['data']['list'] = $list;

        $this->_success('v1.Cate:cateListNav');
    }

    //分类商品列表
    public function cateGoodsList(){
        $one_cate_id = (int)$this->_param('one_cate_id');//一级
        $two_cate_id = (int)$this->_param('two_cate_id');//二级不传默认为0
        $page = $this->_param('page');
        $pagesize = $this->_param('pagesize');
        $offset = empty($page)?$page*$pagesize:($page-1)*$pagesize;
        $limit = $pagesize;

        $where = array();
        $where['a.is_on_sale'] = 1;
        $where['a.check'] = 1;
        $where['b.is_seleted'] = 1;
        if($two_cate_id){
            $where['a.cat_id'] = $two_cate_id;
        }else{
            $categroyService = new CategoryService();
            $categoryList = $categroyService->getThreeCategoryNav($one_cate_id);
            $cat_ids = array_column($categoryList,'id');
            $where['a.cat_id'] = array('in',$cat_ids);
        }

        $orderby = "a.goods_id desc";

        $goodsModel = new GoodsModel();
        $goodsService = new GoodsService();
        $liveService = new LiveService();

        $count = $goodsModel->getGoodsListByWhereCount($where);
        $hasnext = $count>$pagesize?1:0;
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

        $this->_success('v1.Cate:cateGoodsList');
    }
}