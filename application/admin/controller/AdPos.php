<?php
namespace app\admin\controller;
use app\common\model\AdPosition;
use library\Response;
class AdPos extends Base{

    //列表
    public function list_view(){
        if($this->request->isPost()){
            $this->request->filter(['strip_tags', 'htmlspecialchars']);
            $page      = (int)$this->request->post('page', 0);
            $limit      = (int)$this->request->post('limit', 10);
            $offset = empty($page)?0:($page-1)*$limit;
            $condition = [];
            $adPositionModel = new AdPosition();
            $count = $adPositionModel->fetchCount($condition);
            $list  = array();
            if ( 0 < $count ) {
                $list = $adPositionModel->fetchList($condition,$offset,$limit);
            }
             Response::Json(0,"请求成功",$count,$list);
        }
        return $this->view->fetch();
    }

    //添加
    public function add(){
        if($this->request->isPost()){
            $params = $this->request->param();
            $adPositionModel = new AdPosition();
            $res = $adPositionModel->toAdd($params);
            if ($res['code']==0) {
                $this->error($res['msg']);
            }
            $this->success($res['msg']);
        }
        return $this->view->fetch();
    }

    //编辑
    public function edit(){
        $adPositionModel = new AdPosition();
        if($this->request->isPost()){
            $params = $this->request->param();
            $res = $adPositionModel->toAdd($params);
            if ($res['code']==0) {
                $this->error($res['msg']);
            }
            $this->success($res['msg']);
        }
        $id = (int)$this->request->get('id');
        $info = $adPositionModel -> getDetail(array('id'=>$id));
        $this->assign('info', $info);
        return $this->view->fetch();
    }

    //删除
    public function delete_data(){
        if($this->request->isPost()){
            $id = (int)$this->request->post('id');
            $adPositionModel = new AdPosition();
            $res = $adPositionModel->drop(array('id'=>$id));
            if (false === $res) {
                $this->error("删除失败");
            }
            $this->success("删除成功");
        }
    }

}
