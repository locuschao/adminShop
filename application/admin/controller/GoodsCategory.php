<?php
// +----------------------------------------------------------------------
// | 商品分类
// +----------------------------------------------------------------------
// | Author: pc 2020-02-06
// +----------------------------------------------------------------------
namespace app\admin\controller;
use app\common\model\GoodsCategory as GoodsCategoryModel;
use library\Response;
use think\Db;

class GoodsCategory extends Base{
    //列表
    public function list_view(){
        if($this->request->isPost()){
            $this->request->filter(['strip_tags', 'htmlspecialchars']);

            $page      = (int)$this->request->post('page', 0);
            $limit      = (int)$this->request->post('limit', 0);
            $offset = empty($page)?0:($page-1)*$limit;
            $condition = [];

            $goodsCategoryModel = new GoodsCategoryModel();
            $count = $goodsCategoryModel->fetchCount($condition);
            $list  = array();
            if ( 0 < $count ) {
                $list = $goodsCategoryModel->fetchList($condition,$offset,$limit);
            }
            Response::Json(0,"请求成功",$count,$list);
        }
        return $this->view->fetch();
    }

    //添加
    public function add(){
        if($this->request->isPost()){
            $param = $this->request->param();
            $name  = $this->request->post('name');
            $pid   = (int)$this->request->post('pid');
            $goodsCategoryModel = new GoodsCategoryModel();
            if ( $goodsCategoryModel->getDetail(array("name"=>$name)) ) {
                $this->error('分类名称已存在');
            }
            $validate = new \app\common\validate\GoodsCategory();
            $result = $validate->scene('save')->check($param);
            if(!$result){
                $this->error($validate->getError());
            }
            Db::startTrans();
            $id = $goodsCategoryModel->insertGetId($param);

            if ($id < 0) {
                Db::rollback();
                $this->error("添加失败");
            }

            if($pid>0){
                $cateInfo = $goodsCategoryModel->getDetail(array('id'=>$pid));
                $pid = empty($cateInfo)?0:$cateInfo['pid_id_path'];
            }

            $pid_id_path = $pid."_".$id;
            $update_data['level'] = count(explode('_',$pid_id_path))-1;
            $update_data['pid_id_path'] = $pid_id_path;
            $res = $goodsCategoryModel->update($update_data,array('id'=>$id));
            if (false === $res) {
                Db::rollback();
                $this->error("添加失败");
            }
            Db::commit();
            $this->success("添加成功");
        }

        return $this->view->fetch();
    }

    //编辑
    public function edit(){
        $id = $this->request->get('id');
        $goodsCategoryModel = new GoodsCategoryModel();
        if($this->request->isPost()){
            $param = $this->request->param();

            $validate = new \app\common\validate\GoodsCategory();
            $result = $validate->scene('save')->check($param);
            if(!$result){
                $this->error($validate->getError());
            }
            $id  = (int)$this->request->post('id');
            $pid = (int)$this->request->post('pid');
            $name = $this->request->post('name');
            $image = $this->request->post('image');
            $weight = (int)$this->request->post('weight');
            $is_show = (int)$this->request->post('is_show');
            $data['pid'] = $pid;
            $data['name'] = $name;
            $data['image'] = $image;
            $data['weight'] = $weight;
            $data['is_show'] = $is_show;
            $cateInfo = $goodsCategoryModel->getDetail(array('id'=>$pid));
            $pid = empty($cateInfo)?0:$cateInfo['pid_id_path'];
            $pid_id_path = $pid."_".$id;
            $data['level'] = count(explode('_',$pid_id_path))-1;
            $data['pid_id_path'] = $pid_id_path;
            $res = $goodsCategoryModel->update($data,array('id'=>$id));
            if (false === $res) {
                $this->error("更改失败");
            }
            $this->success("更改成功");
        }
        $info = $goodsCategoryModel->getDetail(array("id"=>$id));
        $this->assign('info', $info);
        return $this->view->fetch();
    }

    //删除
    public function delete_data(){

    }
}