<?php
// +----------------------------------------------------------------------
// | 商品图片
// +----------------------------------------------------------------------
// | Author: pc 2020-02-06
// +----------------------------------------------------------------------
namespace app\admin\controller;
use library\Response;
use think\Db;
class GoodsImage extends Base{

    //添加图片
    public function add(){
        $goodsImageModel = new \app\common\model\GoodsImage();
        $goods_id = (int) $this->request->param('goods_id');
        if($this->request->isPost()){
            $goodsImage = $this->request->param();
            Db::startTrans();
            $delete = $goodsImageModel->drop(array('goods_id'=>$goods_id));
            if(false === $delete){
                Db::rollback();
                $this->error("添加失败");
            }
            //添加规格
            $is_select = array();
            if(!empty($goodsImage['src'])){
                $image_data = array();
                foreach ($goodsImage['src'] as $key => $value){
                    $t = array();
                    $t['goods_id'] = $value['goods_id'];
                    $t['src'] = $value['src'];
                    $is_select[] = $t['is_seleted'] = isset($value['LAY_CHECKED'])?1:0;
                    $image_data[] = $t;
                }
            }
            if(!in_array(1,$is_select)){
                $this->error("请选择主图");
            }
            $add = $goodsImageModel->insertAll($image_data);
            if(!$add){
                Db::rollback();
                $this->error("添加失败");
            }
            Db::commit();
            $this->success("添加成功");
        }
        $goodsModel = new \app\common\model\Goods();
        $goods = $goodsModel->getDetail(array("goods_id"=>$goods_id),"goods_sn,goods_name");
        $count = $goodsImageModel->fetchCount(array('goods_id'=>$goods_id));
        $this->assign('goods',$goods);
        $this->assign('goods_id', $goods_id);
        $this->assign('count', $count);
        return $this->view->fetch();
    }

    public function images(){
        if($this->request->isAjax()){
            $goodsImageModel = new \app\common\model\GoodsImage();
            $goods_id      = (int)$this->request->param('goods_id');
            $page      = (int)$this->request->post('page', 0);
            $limit      = (int)$this->request->post('limit', 0);
            $offset = empty($page)?0:($page-1)*$limit;
            $list = array();
            $count = $goodsImageModel -> fetchCount(array('goods_id'=>$goods_id));
            if ( 0 < $count ) {
                $list = $goodsImageModel->fetchList(array('goods_id'=>$goods_id),$offset,$limit);
            }
            Response::Json(0,"请求成功",$count,$list);
        }
    }
}