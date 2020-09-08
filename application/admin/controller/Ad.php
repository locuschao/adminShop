<?php
// +----------------------------------------------------------------------
// | 广告位
// +----------------------------------------------------------------------
// | Author: pc 2020-02-06
// +----------------------------------------------------------------------
namespace app\admin\controller;
use app\common\model\Ad as AdModel;
use library\Response;

class Ad extends Base{

    //列表
    public function list_view(){
        if($this->request->isPost()){
            $this->request->filter(['strip_tags', 'htmlspecialchars']);

            $page      = (int)$this->request->post('page', 0);
            $pos_id      = (int)$this->request->post('pos_id', 0);
            $limit      = (int)$this->request->post('limit', 0);
            $offset = empty($page)?0:($page-1)*$limit;
            $condition = [];
            if(!empty($pos_id)){
                $condition['pos_id'] = $pos_id;
            }

            $adModel = new AdModel();
            $count = $adModel->fetchCount($condition);
            $list  = array();
            if ( 0 < $count ) {
                $list = $adModel->fetchList($condition,$offset,$limit);
            }
            Response::Json(0,"请求成功",$count,$list);
        }
        return $this->view->fetch();
    }

    //添加
    public function add(){
        if($this->request->isPost()){
            $param = $this->request->param();
            $adModel = new AdModel();
            $res = $adModel->toAdd($param);
            if ($res['code'] == 0) {
                $this->error($res['msg']);
            }
            $this->success("添加成功");

        }
        return $this->view->fetch();
    }

    //编辑
    public function edit(){
        $id = $this->request->get('id');
        $adModel = new AdModel();
        if($this->request->isPost()){
            $params = $this->request->param();
            $res = $adModel->toAdd($params);
            if ($res['code']==0) {
                $this->error($res['msg']);
            }
            $this->success("更新成功");
        }
        $info = $adModel -> getDetail(array('id'=>$id));
        $this->assign('info', $info);
        return $this->view->fetch();
    }

    //删除
    public function delete_data(){
        if($this->request->isPost()){
            // 接收参数
            $id = $this->request->post('id');

            $adModel = new AdModel();
            $res = $adModel->where(array('id'=>$id))->delete();
            if (false ===  $res) {
                $this->error("删除失败");
            }
            $this->success("删除成功");
        }
    }
}