<?php
// +----------------------------------------------------------------------
// | 商品模型
// +----------------------------------------------------------------------
// | Author: pc 2020-02-06
// +----------------------------------------------------------------------
namespace app\admin\controller;
use app\common\model\GoodsAttribute;
use app\common\model\GoodsAttrType;
use app\common\model\Spec;
use app\common\model\SpecItem;
use library\Response;
use think\Db;

class Attribute extends Base{
    //列表
    public function list_view(){
        if($this->request->isPost()){
            $this->request->filter(['strip_tags', 'htmlspecialchars']);

            $page      = (int)$this->request->post('page', 0);
            $limit      = (int)$this->request->post('limit', 0);
            $offset = empty($page)?0:($page-1)*$limit;
            $condition = [];

            $goodsAttrTypeModel = new GoodsAttrType();
            $count = $goodsAttrTypeModel->fetchCount($condition);
            $list  = array();
            if ( 0 < $count ) {
                $list = $goodsAttrTypeModel->fetchList($condition,$offset,$limit);
            }
            Response::Json(0,"请求成功",$count,$list);
        }
        return $this->view->fetch();
    }

    //添加
    public function add(){
        if($this->request->isPost()){
            $this->request->filter(['strip_tags', 'htmlspecialchars']);
            $name = $this->request->post('name');
            if(empty($name)){
                $this->error('属性名称不能为空');
            }
            $goodsAttrTypeModel = new GoodsAttrType();
            if ( $goodsAttrTypeModel->fetchCount(array('name'=>$name)) ) {
                $this->error('分类名称已存在');
            }
            Db::startTrans();
            $id = $goodsAttrTypeModel->insertGetId(array('name'=>$name));
            if ($id < 0) {
                Db::rollback();
                $this->error("添加失败");
            }
            //添加属性
            $attr = $this->request->post('attr/a');
            if(!empty($attr)){
                foreach ($attr as &$value){
                    if(empty($value['attr_name'])){
                        Db::rollback();
                        $this->error("产品参数不能为空");
                    }
                    if(empty($value['order'])){
                        Db::rollback();
                        $this->error("排序不能为空");
                    }
                    $value['type_id'] = $id;
                }
                $goodsAttributeModel = new GoodsAttribute();
                $add_attr = $goodsAttributeModel->insertAll($attr);
                if (!$add_attr) {
                    Db::rollback();
                    $this->error("添加失败");
                }
            }

            //添加规格
            $spec = $this->request->post('spec/a');
            if(!empty($spec)){
                $specModel = new Spec();
                $spec_item_data = array();
                foreach ($spec as $value){
                    if(empty($value['name'])){
                        Db::rollback();
                        $this->error("规格值不能为空");
                    }
                    if(empty($value['order'])){
                        Db::rollback();
                        $this->error("排序不能为空");
                    }
                    $spec_id = $specModel->insertGetId(array('type_id'=>$id,'name'=>$value['name'],'order'=>$value['order']));
                    if(!empty($value['specItem'])){
                        foreach ($value['specItem'] as $item){
                            if(empty($item['value'])){
                                Db::rollback();
                                $this->error("子规格不能为空");
                            }
                            $t = array();
                            $t['spec_id'] = $spec_id;
                            $t['item'] = $item['value'];
                            $t['order_index'] = $item['order'];
                            $spec_item_data[] = $t;
                        }
                    }
                }
                $add_spec = true;
                //添加子类
                if(!empty($spec_item_data)){
                    $specItemModel = new SpecItem();
                    $add_spec = $specItemModel->insertAll($spec_item_data);
                }
                if (!$add_spec) {
                    Db::rollback();
                    $this->error("添加失败");
                }

            }
            Db::commit();
            $this->success("添加成功");
        }
        return $this->view->fetch();
    }

    //编辑
    public function edit(){
        $id = $this->request->get('id');
        $goodsAttrTypeModel = new GoodsAttrType();
        $info = $goodsAttrTypeModel->getDetail(array("id"=>$id));
        $specModel = new Spec();
        $where = array("type_id"=>$id);
        $spec = $specModel->fetchList($where,0,50);
        $spec_count = $specModel -> fetchCount($where);
        $goodsAttributeModel = new GoodsAttribute();
        $attr = $goodsAttributeModel->fetchList($where,0,50);
        $attr_count = $goodsAttributeModel->fetchCount($where);
        $this->assign('spec', $spec);
        $this->assign('spec_count', $spec_count);
        $this->assign('attr', $attr);
        $this->assign('attr_count', $attr_count);
        $this->assign('info', $info);
        $this->assign('type_id', $id);
        return $this->view->fetch();
    }

    //保存
    public function save(){
        if($this->request->isPost()){
            // 接收参数
            $name = $this->request->post('name');
            $id = $this->request->post('id');
            if(empty($name)){
                $this->error('属性名称不能为空');
            }

            $goodsAttrTypeModel = new GoodsAttrType();
            Db::startTrans();
            $res = $goodsAttrTypeModel->update(array('name'=>$name),array('id'=>$id));
            if (false === $res) {
                Db::rollback();
                $this->error("更新失败");
            }
            $specItemModel = new SpecItem();
            //添加属性
            $attr = $this->request->post('attr/a');
            $attr_data = array();
            if(!empty($attr)){
                foreach ($attr as $value){
                    if(empty($value['attr_name'])){
                        Db::rollback();
                        $this->error("产品参数不能为空");
                    }
                    if(empty($value['order'])){
                        Db::rollback();
                        $this->error("排序不能为空");
                    }
                    if(empty($value['attr_id'])){
                        $t = array();
                        $t['type_id'] = $id;
                        $t['attr_name'] = $value['attr_name'];
                        $t['order'] = $value['order'];
                        $attr_data[] = $t;
                    }
                }
                $add_attr = true;
                if(!empty($attr_data)){
                    $goodsAttributeModel = new GoodsAttribute();
                    $add_attr = $goodsAttributeModel->insertAll($attr_data);
                }
                if (!$add_attr) {
                    Db::rollback();
                    $this->error("添加失败");
                }
            }
            //规格目前最多三个
            $spec = $this->request->post('spec/a');
            if(count($spec)<=3 && !empty($spec)){
                $specModel = new Spec();
                $spec_item_data = array();

                foreach ($spec as $key => $value){
                    if(empty($value['name'])){
                        Db::rollback();
                        $this->error("规格值不能为空");
                    }
                    if(empty($value['order'])){
                        Db::rollback();
                        $this->error("排序不能为空");
                    }
                    if(empty($value['id'])){
                        if(!empty($value['specItem'])){
                            $spec_id = $specModel->insertGetId(array('type_id'=>$id,'name'=>$value['name'],'order'=>$value['order']));
                            foreach ($value['specItem'] as $item){
                                if(empty($item['value'])){
                                    Db::rollback();
                                    $this->error("子规格不能为空");
                                }
                                if(empty($item['id'])){
                                    $t = array();
                                    $t['spec_id'] = $spec_id;
                                    $t['item'] = $item['value'];
                                    $t['order_index'] = $item['order'];
                                    $spec_item_data[] = $t;
                                }
                            }
                        }
                    }else{
                        if(!empty($value['specItem'])){
                            foreach ($value['specItem'] as $item){
                                if(empty($item['value'])){
                                    Db::rollback();
                                    $this->error("子规格不能为空");
                                }
                                if(empty($item['id'])){
                                    $t = array();
                                    $t['spec_id'] = $key;
                                    $t['item'] = $item['value'];
                                    $t['order_index'] = $item['order'];
                                    $spec_item_data[] = $t;
                                }else{
                                    $specItemModel->update(array('item'=>$item['value'],'order_index'=>$item['order']),array('id'=>$item['id']));
                                }

                            }
                        }
                    }
                }

                $add_spec = true;
                //添加子类
                if(!empty($spec_item_data)){
                    $add_spec = $specItemModel->insertAll($spec_item_data);
                }
                if (!$add_spec) {
                    Db::rollback();
                    $this->error("添加失败");
                }

            }

            Db::commit();
            $this->success("更新成功");

        }
    }

    //删除
    public function delete_data(){

    }
}