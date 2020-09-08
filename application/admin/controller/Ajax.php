<?php
// +----------------------------------------------------------------------
// | ajax
// +----------------------------------------------------------------------
// | Author: pc
// +----------------------------------------------------------------------
namespace app\admin\controller;
use app\common\model\GoodsAttr;
use app\common\model\GoodsAttrType;
use app\common\model\GoodsCategory as GoodsCategoryModel;
use app\common\model\Spec;
use app\common\model\SpecItem;
use app\common\model\SpecGoodsPrice as SpecGoodsPriceModel;
use app\common\model\Goods as GoodsModel;

class Ajax extends Base{

    //ajax获取商品分类
    public function ajaxGetCategory(){
        $result = array(
            'code'=> 0,
            'msg'=> "无数据",
            'data'=> array(),
        );
        if($this->request->isPost()){
            $this->request->filter(['strip_tags', 'htmlspecialchars']);
            $id      = (int)$this->request->post('id', 0);
            $goodsCategoryModel = new GoodsCategoryModel();
            $list = $goodsCategoryModel->where(array('pid'=>$id))->select();
            if(empty($list)){
                echo  json_encode($result);die;
            }
            $result['code'] = 1;
            $result['msg'] = '请求成功';
            $result['data'] = $list->toArray();
            echo  json_encode($result);die;
        }
        echo  json_encode($result);die;

    }

    //ajax获取属性和属性值
    public function ajaxGetAttr(){
        $result = array(
            'code'=> 0,
            'msg'=> "无数据",
            'data'=> array(),
        );
        if($this->request->isPost()){
            $this->request->filter(['strip_tags', 'htmlspecialchars']);
            $goods_type      = (int)$this->request->post('goods_type', 0);
            $goods_id      = (int)$this->request->post('goods_id', 0);

            $goodsAttrModel = new GoodsAttr();
            $goodsAttrTypeModel = new GoodsAttrType();
            $list = $goodsAttrTypeModel
                ->alias('a')
                ->join('cc_goods_attribute b','a.id=b.type_id','left')
                ->where(array('a.id'=>$goods_type))
                ->select();
            if(empty($list)){
                echo  json_encode($result);die;
            }
            $list = $list->toArray();
            $goods_attr_list = $goodsAttrModel->where(array('goods_id'=>$goods_id))->select();
            $goods_attr_list = empty($goods_attr_list)?array():$goods_attr_list->toArray();
            $goods_attr_list = array_column($goods_attr_list,'attr_value','attr_id');
            foreach ($list  as &$value){
                $value['attr_value']  = empty($goods_attr_list[$value['attr_id']])?"":$goods_attr_list[$value['attr_id']];
            }
            $result['code'] = 1;
            $result['msg'] = '请求成功';
            $result['data'] = $list;
            echo  json_encode($result);die;
        }
        echo  json_encode($result);die;
    }

    //ajax获取图片列表
    public function images(){
        $result = array(
            'code'=> 0,
            'msg'=> "无数据",
            'data'=> array(),
        );
        if($this->request->isAjax()){
            $goodsImageModel = new \app\common\model\GoodsImage();
            $goods_id      = (int)$this->request->param('goods_id');
            $list = $goodsImageModel->where(array('goods_id'=>$goods_id))->select();
            $list = empty($list)?array():$list->toArray();
            $result['code'] = 1;
            $result['msg'] = '请求成功';
            $result['data'] = $list;
            echo  json_encode($result);die;
        }
        echo  json_encode($result);die;
    }

    //ajax获取规格组合
    public function getGoodsSpec(){
        $result = array(
            'code'=> 0,
            'msg'=> "无数据",
            'data'=> array(),
        );
        $type_id = (int)$this->request->param('goods_type');
        $goods_id = (int)$this->request->param('goods_id');
        $is_check = array();
        if(!empty($goods_id)){
          $specGoodsPriceModel = new SpecGoodsPriceModel();
          $goods_list = $specGoodsPriceModel->getSpecGoodsPriceInfoByGoodsId($goods_id);
          if(!empty($goods_list)){
              foreach ($goods_list as $value){ //
                  $keyList = explode('_',$value['key']);
                  foreach ($keyList as $vv){
                      array_push($is_check,$vv);
                  }
              }
              unset($goods_list);
              unset($keyList);
          }
        }

        $is_check = array_unique($is_check);

        if($this->request->isPost()){
            $specModel = new Spec();
            $specItemModel = new SpecItem();
            $spec = $specModel->fetchList(array("type_id"=>$type_id),0,4);
            if(!empty($spec)){
                foreach ($spec as &$value){
                    $specItem = $specItemModel->where(array('spec_id'=>$value['id']))->select();
                    $value['specItem'] = empty($specItem)?array():$specItem->toArray();
                    foreach ($value['specItem'] as &$val){
                        if(in_array($val['id'],$is_check)){
                            $val['is_check'] = 1;
                        }else{
                            $val['is_check'] = 0;
                        }
                    }
                }
            }
            $result['code'] = 1;
            $result['msg'] = '请求成功';
            $result['data'] = $spec;
            echo  json_encode($result);die;
        }
        echo  json_encode($result);die;
    }

    //ajax获取组合sku
    public function getGoodsSku(){
        $result = array(
            'code'=> 0,
            'msg'=> "无数据",
            'data'=> array(),
        );
        if($this->request->isPost()){
            $param = $this->request->param();
            $goods_id = $this->request->param('goods_id');
            $goodsModel = new GoodsModel();
            $goods = $goodsModel->getDetail(array('goods_id'=>$goods_id),'shop_price');
            $specItem = empty($param)?array():$param['specItem'];
            $spec_merge_data = array();
            foreach ($specItem as $k=>$v){
                $t = array();
                $i = 0;
                foreach ($v as $kk=>$vv){
                    if(isset($vv['id'])){
                       $t[$i]['id'] = $kk;
                       $t[$i]['item'] = $vv['name'];
                       $i++;
                    }
                }

                $spec_merge_data[] = $t;
            }
            if(!empty($spec_merge_data)){
                foreach ($spec_merge_data as $k=>$v){
                    if(empty($v)){
                        unset($spec_merge_data[$k]);
                    }
                }
            }
            $list = !empty(array_values($spec_merge_data))?combineAttributes(array_values($spec_merge_data)):array();
            if(!empty($list)){
                foreach ($list as &$value){
                    $value['price'] = $goods['shop_price'];
                }
            }
            $result['code'] = 1;
            $result['msg'] = '请求成功';
            $result['data'] = $list;
            echo  json_encode($result);die;
        }
        echo  json_encode($result);die;
    }

    //获取选中的sku
    public function getGoodsSpecPrice(){
        $result = array(
            'code'=> 0,
            'msg'=> "无数据",
            'data'=> array(),
        );
        $goods_id = $this->request->param('goods_id');
        if($this->request->isPost()){
            $specGoodsPriceModel = new SpecGoodsPriceModel();
            $list = $specGoodsPriceModel->getSpecGoodsPriceInfoByGoodsId($goods_id);
            $result['code'] = 1;
            $result['msg'] = '请求成功';
            $result['data'] = $list;
            echo  json_encode($result);die;
        }
        echo  json_encode($result);die;
    }

    //批量更新
    public function batch_save(){
        $result = array(
            'code'=> 0,
            'msg'=> "无数据",
            'data'=> array(),
        );
        $type = (int)$this->request->param('type');
        $ids = $this->request->param('ids/a');
        $check = (int)$this->request->param('check');
        $goods_ids = array_filter($ids,'intval');
        if(empty($goods_ids) || !in_array($type,[1,2])){
            $result['msg'] = '数据异常';
            echo  json_encode($result);die;
        }

        $where['goods_id'] = array('in',$ids);
        $data = array();

        switch ($type){
            case 1://批量审核
                $data = array('check'=>$check);
                break;
            case 2://批量发布
                $data = array('is_on_sale'=>$check);
                break;
        }
        $goodsModel = new \app\common\model\Goods();
        $res = $goodsModel->update($data,$where);

        if(false === $res){
            $result['msg'] = '更新失败';
            echo  json_encode($result);die;
        }

        $result['code'] = 1;
        $result['msg'] = '更新成功';
        echo  json_encode($result);die;
    }
}