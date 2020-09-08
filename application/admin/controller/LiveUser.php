<?php
namespace app\admin\controller;
use app\common\model\LiveUser as LiveUserModel;

class LiveUser extends Base{

    public function userList(){
        $result = array(
            'code'=> 0,
            'msg'=> "",
            'count'=> 0,
            'data'=> array(),
        );

        if($this->request->isPost()){
            $this->request->filter(['strip_tags', 'htmlspecialchars']);

            $nickname = $this->request->post('nickname');
            $username = $this->request->post('username');
            $page      = (int)$this->request->post('page', 0);
            $limit      = (int)$this->request->post('limit', 0);
            $offset = empty($page)?0:($page-1)*$limit;
            $condition = [];
            if(!empty($nickname)){
                $condition['nickname'] = $nickname;
            }
            if(!empty($username)){
                $condition['username'] = $username;
            }

            $liveUserModel = new LiveUserModel();
            $count = $liveUserModel->fetchCount($condition);
            $list  = array();
            if ( 0 < $count ) {
                $list = $liveUserModel->fetchList($condition,$offset,$limit);
            }
            $result['count'] = $count;
            $result['data'] = $list;
            echo  json_encode($result);die;
        }

        return $this->view->fetch();
    }

    public function ajaxUpdateLiveUser(){
        if($this->request->isPost()){
            // 接收参数
            $id = $this->request->post('id');
            $status = $this->request->post('status');

            $liveUserModel = new LiveUserModel();
            $res = $liveUserModel->update(array('status'=>$status),array('id'=>$id));

            if (false === $res) {
                $this->error("更新失败");
            }
            $this->success("更新成功");
        }
    }
}