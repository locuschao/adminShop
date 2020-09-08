<?php
/**
 * [活动管理]
 */
namespace app\admin\controller;
use library\Response;
use library\XunSearch;
use app\common\model\GoodsActivity as GoodsActivityModel;
use app\common\model\GoodsActivityRelate as GoodsActivityRelateModel;
use think\Db;

class GoodsActivity extends Base{
    //列表
    public function list_view(){
        if($this->request->isPost()){
            $this->request->filter(['strip_tags', 'htmlspecialchars']);
            $xunSearch = new XunSearch();
            $keywords   = $this->request->post('keywords');
            $status      =  $this->request->post('status');
            $type      =  $this->request->post('type');
            $ids = $xunSearch ->searchGoodsActivity($keywords);
            $page      = (int)$this->request->post('page', 0);
            $limit      = (int)$this->request->post('limit', 0);
            $offset = empty($page)?0:($page-1)*$limit;

            $condition = [];
            if(!empty($keywords)){
                $condition['a.id'] = array('in',$ids);
            }
            if(isset($status) && $status>=0){
                $condition['a.status'] = $status;
            }
            if(isset($type) && $type>=0){
                $condition['b.type'] = $type;
            }
            $goodsActivityModel = new GoodsActivityModel();
            $count = $goodsActivityModel->fetchCount($condition);
            $list  = array();
            if ( 0 < $count ) {
                $list = $goodsActivityModel->fetchList($condition,$offset,$limit);
            }
            Response::Json(0,"请求成功",$count,$list);
        }
        return $this->view->fetch();
    }

    //添加
    public function add(){
        if($this->request->isPost()){
            $data['title'] = trim($this->request->post('title'));
            $data['coupon_id'] = (int)$this->request->post('coupon_id');
            $data['start_time'] = strtotime($this->request->post('start_time'));
            $data['end_time'] = strtotime($this->request->post('end_time'));
            $data['type'] = (int)$this->request->post('type');
            $data['get_num'] = (int)$this->request->post('get_num');
            $data['status'] = (int)$this->request->post('status');
            $data['create_time'] = time();
            $validate = new \app\common\validate\GoodsActivity();
            $result = $validate->scene('save')->check($data);
            if(!$result){
                $this->error($validate->getError());
            }
            $goodsActivityModel = new GoodsActivityModel();
            $id = $goodsActivityModel->insertGetId($data);
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
            $data['status'] = (int)$this->request->post('status');
            $goodsActivityModel = new GoodsActivityModel();
            $res = $goodsActivityModel->update($data,array('id'=>$id));
            if(false === $res){
                $this->error('更新失败');
            }
            $this->success('更新成功');
        }
        $goodsActivityModel = new GoodsActivityModel();
        $info = $goodsActivityModel->getDetail(array('a.id'=>$id));
        $this->assign('info',$info);
        return $this->view->fetch();
    }

    //关联商品
    public function relate_goods(){
        $id = (int)$this->request->param('id');
        $goodsActivityModel = new GoodsActivityModel();
        $goodsActivityRelateModel = new GoodsActivityRelateModel();

        //查询关联的商品
        $goodsActivityRelateList = $goodsActivityRelateModel->fetchList(array('goods_activity_id'=>$id),0,1000);
        $goods_ids = array_column($goodsActivityRelateList,'goods_id');
        $info = $goodsActivityModel->getDetail(array('a.id'=>$id));

        //查询当前活动时间内是否绑定可选商品
        $start_time = strtotime($info['start_time']);
        $end_time = strtotime($info['end_time']);
        $type = $info['type'];
        $sql = "select * from cc_goods_activity_relate where (start_time <= $start_time and end_time >= $start_time and type = $type and goods_activity_id !=$id ) or (start_time <= $end_time and end_time >= $end_time and type = $type and goods_activity_id !=$id) ";
        $goodsActivityGoods = $goodsActivityRelateModel->query($sql);
        $no_goods_ids = array_column($goodsActivityGoods,'goods_id');
        if($this->request->isPost()){
            $goods_ids = $this->request->param('goods_id');
            $data = array();
            if(!empty($goods_ids)){
                $goods_ids = explode(',',$goods_ids);
                foreach ($goods_ids as $value){
                    $t=array();
                    $t['goods_activity_id']=$id;
                    $t['goods_id']=$value;
                    $t['start_time']=$start_time;
                    $t['end_time']=$end_time;
                    $t['type']=$info['type'];
                    $data[] = $t;
                }
            }

            Db::startTrans();
            $goodsActivityRelateModel->where(array('goods_activity_id'=>$id))->delete();
            $res = $goodsActivityRelateModel->insertAll($data);
            if(false === $res){
                Db::rollback();
                $this->error('添加失败');
            }
            Db::commit();
            $this->success('添加成功');
        }
        $this->assign('info',$info);
        $this->assign('goods_ids',$goods_ids);
        $this->assign('no_goods_ids',$no_goods_ids);
        return $this->view->fetch();
    }

    //查看
    public function view(){
        $id = $this->request->param('id');
        if($this->request->isPost()){
            $this->request->filter(['strip_tags', 'htmlspecialchars']);
            $id = $this->request->param('id');
            $page      = (int)$this->request->post('page', 0);
            $limit      = (int)$this->request->post('limit', 0);
            $offset = empty($page)?0:($page-1)*$limit;
            $condition = [];
            $condition['a.goods_activity_id'] = $id;
            $goodsActivityRelateModel = new GoodsActivityRelateModel();
            $count = $goodsActivityRelateModel->fetchCount($condition);
            $list  = array();
            if ( 0 < $count ) {
                $list = $goodsActivityRelateModel->fetchList($condition,$offset,$limit);
            }
            Response::Json(0,"请求成功",$count,$list);
        }
        $this->assign('id',$id);
        return $this->view->fetch();
    }

    //批量审核
    public function batch_check(){
        if($this->request->isPost()){
            $ids = $this->request->param('ids/a');
            $status = (int)$this->request->param('check');
            $goodsActivityModel = new GoodsActivityModel();
            $res = $goodsActivityModel->update(array('status'=>$status),array('id'=>array('in',$ids)));
            if(false === $res){
                $this->error('更新失败');
            }
            $this->success('更新成功');
        }
        return $this->view->fetch();
    }
}