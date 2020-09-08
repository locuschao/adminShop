<?php
// +----------------------------------------------------------------------
// | 商品选择小部件
// +----------------------------------------------------------------------
// | Author: Wuyh 2020-02-05
// +----------------------------------------------------------------------

namespace app\admin\widget;

use app\common\model\Brand;
use app\common\model\FreightTemplate;
use app\common\model\GoodsAttrType;
use app\common\model\GoodsCategory as GoodsCategoryModel;
use app\common\model\Goods as GoodsModel;
use library\Tree;

class Goods extends BaseWidget
{
    public function _initialize()
    {
        parent::_initialize();
    }

    /**
     * 商品选择
     * @param $name
     * @return mixed
     */
    public function goodsSelect($name, $goods, $maxNum = 10,$no_goods_ids = array())
    {
        $time = "g".time().rand(1,4);
        $maxNum = $maxNum;
        $goodsIds = '';

        if ($goods){
            $goodList = [];
            foreach ($goods as $good){
                $goodList[] = [
                    'id' => $good['id'],
                    'goods_id' => $good['goods_id'],
                    'goods_sn' => $good['goods_sn'],
                    'shop_price' => $good['shop_price'],
                    'goods_name' => $good->goods->goods_name,
                ];
            }
            $goodsIds = implode(',' ,array_column($goodList, 'goods_id'));
            $goods = json_encode($goodList);
        }
        $no_goods_ids = empty($no_goods_ids)?0:implode(',' ,$no_goods_ids);
        $this->assign('name', $name);
        $this->assign('time', $time);
        $this->assign('maxNum', $maxNum);
        $this->assign('goods', $goods);
        $this->assign('goodsIds', $goodsIds);
        $this->assign('no_goods_ids', $no_goods_ids);

        return $this->fetch("widget/goods/goods_select");
    }

    /**
     * 商品选择2
     * @param string $name
     * @param array $goods_ids [商品goods_id]
     * @param array $no_goods_ids [排除的id]
     * @return int mixed
     */
    public function goodsSelect2($name, $goods_ids, $num = 1,$no_goods_ids = array())
    {
        $time = "g".time().rand(1,4);
        if(!is_array($goods_ids)){
            $goods_ids = array($goods_ids);
        }
        $goodsModel = new GoodsModel();
        $where['goods_id'] = array('in',$goods_ids);
        $where['is_on_sale'] = 1;
        $where['check'] = 1;
        $list = $goodsModel->fetchList($where,0,10);
        $data = array();
        if(!empty($list)){
            foreach ($list as $value){
                $t= array();
                $t['id'] = $value['goods_id'];
                $t['goods_id'] = $value['goods_id'];
                $t['goods_sn'] = $value['goods_sn'];
                $t['shop_price'] = $value['shop_price'];
                $t['goods_name'] = $value['goods_name'];
                $data[] = $t;
            }
        }

        $goodsIds = empty($goods_ids)?'':implode(',' ,$goods_ids);
        $no_goods_ids = empty($no_goods_ids)?0:implode(',' ,$no_goods_ids);
        $goods = json_encode($data);
        $this->assign('name', $name);
        $this->assign('time', $time);
        $this->assign('maxNum', $num);
        $this->assign('goods', $goods);
        $this->assign('goodsIds', $goodsIds);
        $this->assign('no_goods_ids', $no_goods_ids);

        return $this->fetch("widget/goods/goods_select");
    }

    //查询分类
    public function getCategory($name,$level,$select_id){
        $categoryModel = new GoodsCategoryModel();
        $categoryList = $categoryModel->field('id,name')->where(array('level'=>$level))->select();
        $categoryList = empty($categoryList)?array():$categoryList->toArray();
        $this->assign('name', $name);
        $this->assign('categoryList', $categoryList);
        $this->assign('select_id', $select_id);
        return $this->fetch("widget/goods/category");
    }

    //查询品牌
    public function getBrand($select_id){
        $brandModel = new Brand();
        $brandList = $brandModel->field('id,name')->select();
        $brandList = empty($brandList)?array():$brandList->toArray();
        $this->assign('brandList', $brandList);
        $this->assign('select_id', $select_id);
        return $this->fetch("widget/goods/brand");
    }

    //查询属性分类
    public function getGoodsType($name,$select_id){
        $goodsTypeModel = new GoodsAttrType();
        $goodsTypeList = $goodsTypeModel->field('id,name')->select();
        $goodsType = empty($goodsTypeList)?array():$goodsTypeList->toArray();
        $this->assign('goodsType', $goodsType);
        $this->assign('name', $name);
        $this->assign('select_id', $select_id);
        return $this->fetch("widget/goods/goods_type");
    }

    //查询运费模板
    public function getFreightTemplate($name,$select_id){
        $freightTemplateModel = new FreightTemplate();
        $freightTemplate = $freightTemplateModel->getAllFreightTemplate();
        $this->assign('freightTemplate', $freightTemplate);
        $this->assign('name', $name);
        $this->assign('select_id', $select_id);
        return $this->fetch("widget/goods/freight_template");
    }

    //获取树形菜单
    public function  getCategoryMenu($name,$select_id){
        $goodsCategoryModel = new GoodsCategoryModel();
        $ruleList = $goodsCategoryModel->getTreeMenu();
        Tree::instance()->init($ruleList);

        $menu = Tree::instance()->getTreeList(Tree::instance()->getTreeArray(0), 'name');
        $ruledata = [0 => "顶级菜单"];
        foreach ($menu as $k => &$v)
        {
            $ruledata[$v['id']] = $v['name'];
        }

        $this->assign('ruledata', $ruledata);
        $this->assign('name', $name);
        $this->assign('select_id', $select_id);
        return $this->fetch("widget/goods/category_menu");
    }
}
