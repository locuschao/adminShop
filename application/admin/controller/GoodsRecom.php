<?php
/**
 * [商品推荐]
 * @author pc
 */
namespace app\admin\controller;
use library\Response;
use library\XunSearch;
use app\common\model\GoodsRecom as GoodsRecomModel;
class GoodsRecom extends Base{

    //列表
    public function list_view(){
        if($this->request->isPost()){
            $this->request->filter(['strip_tags', 'htmlspecialchars']);
            $xunSearch = new XunSearch();
            $keywords   = $this->request->post('keywords');
            $status      =  $this->request->post('status');
            $is_virtual =  $this->request->post('is_virtual');
            $merchants_id =  $this->request->post('merchants_id');
            $pos_id =  $this->request->post('pos_id');
            $goods_ids = $xunSearch ->searchGoods($keywords);
            $page      = (int)$this->request->post('page', 0);
            $limit      = (int)$this->request->post('limit', 0);
            $offset = empty($page)?0:($page-1)*$limit;

            $condition = [];
            if(!empty($keywords)){
                $condition['b.goods_id'] = array('in',$goods_ids);
            }
            if(isset($status) && $status>=0){
                $condition['a.status'] = $status;
            }
            if(isset($is_virtual) && $is_virtual>=0){
                $condition['b.is_virtual'] = $is_virtual;
            }
            if(isset($pos_id) && $pos_id>=0){
                $condition['a.pos_id'] = $pos_id;
            }
            if(isset($merchants_id) && $merchants_id>=0){
                switch ($merchants_id){
                    case 0:
                        $condition['b.merchants_id'] = 0;
                        break;
                    case 1:
                        $condition['b.merchants_id'] = array('>',0);
                        break;
                }
            }

            $goodsRecomModel = new GoodsRecomModel();
            $count = $goodsRecomModel->fetchCount($condition);
            $list  = array();
            if ( 0 < $count ) {
                $list = $goodsRecomModel->fetchList($condition,$offset,$limit);
            }
            Response::Json(0,"请求成功",$count,$list);
        }
        return $this->view->fetch();
    }

    //添加
    public function add(){
        if($this->request->isPost()){
            $data['title'] = trim($this->request->post('title'));
            $data['goods_id'] = (int)$this->request->post('goods_id');
            $data['start_time'] = strtotime($this->request->post('start_time'));
            $data['end_time'] = strtotime($this->request->post('end_time'));
            $data['orderby'] = (int)$this->request->post('orderby');
            $data['status'] = (int)$this->request->post('status');
            $data['pos_id'] = (int)$this->request->post('pos_id');
            $data['image'] = $this->request->post('image');
            $validate = new \app\common\validate\GoodsRecom();
            $result = $validate->scene('save')->check($data);
            if(!$result){
                $this->error($validate->getError());
            }
            $goodsRecomModel = new GoodsRecomModel();
            $id = $goodsRecomModel->insertGetId($data);
            if($id<=0){
                $this->error('添加失败');
            }
            $this->success('添加成功');
        }
        return $this->view->fetch();
    }

    //编辑
    public function edit(){
        $id = (int)$this->request->param('id');
        if($this->request->isPost()){
            $data['title'] = trim($this->request->post('title'));
            $data['goods_id'] = (int)$this->request->post('goods_id');
            $data['start_time'] = strtotime($this->request->post('start_time'));
            $data['end_time'] = strtotime($this->request->post('end_time'));
            $data['orderby'] = (int)$this->request->post('orderby');
            $data['status'] = (int)$this->request->post('status');
            $data['pos_id'] = (int)$this->request->post('pos_id');
            $data['image'] = $this->request->post('image');
            $validate = new \app\common\validate\GoodsRecom();

            $result = $validate->scene('save')->check($data);
            if(!$result){
                $this->error($validate->getError());
            }
            $goodsRecomModel = new GoodsRecomModel();
            $res = $goodsRecomModel->update($data,array('id'=>$id));
            if(false === $res){
                $this->error('更新失败');
            }
            $this->success('更新成功');
        }
        $goodsRecomModel = new GoodsRecomModel();
        $info = $goodsRecomModel->getDetail(array('id'=>$id));
        $this->assign('info',$info);
        return $this->view->fetch();
    }

    //批量审核
    public function batch_check(){
        if($this->request->isPost()){
            $ids = $this->request->param('ids/a');
            $status = (int)$this->request->param('check');
            $goodsRecomModel = new GoodsRecomModel();
            $res = $goodsRecomModel->update(array('status'=>$status),array('id'=>array('in',$ids)));
            if(false === $res){
                $this->error('更新失败');
            }
            $this->success('更新成功');
        }
        return $this->view->fetch();
    }
}