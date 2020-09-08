<?php
// +----------------------------------------------------------------------
// | 品牌管理
// +----------------------------------------------------------------------
// | Author: pc 2020-02-06
// +----------------------------------------------------------------------
namespace app\admin\controller;
use app\common\model\Brand;
use library\Response;

class GoodsBrand extends Base{
    //列表
    public function list_view(){
        if($this->request->isPost()){
            $this->request->filter(['strip_tags', 'htmlspecialchars']);

            $page      = (int)$this->request->post('page', 0);
            $limit      = (int)$this->request->post('limit', 0);
            $offset = empty($page)?0:($page-1)*$limit;
            $condition = [];

            $brandModel = new Brand();
            $count = $brandModel->fetchCount($condition);
            $list  = array();
            if ( 0 < $count ) {
                $list = $brandModel->fetchList($condition,$offset,$limit);
            }
            Response::Json(0,"请求成功",$count,$list);
        }
        return $this->view->fetch();
    }

    //添加
    public function add(){
        if($this->request->isPost()){
            $param = $this->request->param();
            $brandModel = new Brand();
            if ( $brandModel->fetchCount(array('name'=>$param['name'])) ) {
                $this->error('品牌名称已存在');
            }

            $res = $brandModel->toAdd($param);
            if ($res['code'] == 0) {
                $this->error($res['msg']);
            }
            $this->success("添加成功");
        }
        return $this->view->fetch();
    }

    //编辑
    public function edit(){
        $id = (int)$this->request->param('id');
        $brandModel = new Brand();
        if($this->request->isPost()){
            $param = $this->request->param();
            $res = $brandModel->toAdd($param);
            if ($res['code'] == 0) {
                $this->error("更新失败");
            }
            $this->success("更新成功");
        }
        $info = $brandModel->getDetail(array('id'=>$id));
        $this->assign('info', $info);
        return $this->view->fetch();
    }

    //删除
    public function delete_data(){
        if($this->request->isPost()){
            $id = (int)$this->request->post('id');
            $brandModel = new Brand();
            $res = $brandModel->drop(array('id'=>$id));
            if (false === $res) {
                $this->error("删除失败");
            }
            $this->success("删除成功");
        }
    }
}