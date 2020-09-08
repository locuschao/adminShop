<?php
/**
 * [直播项]
 */
namespace app\admin\controller;
use library\Response;
use app\common\model\LiveItem as LiveItemModel;
use app\common\model\Live as LiveModel;
class LiveItem extends Base{
    //列表
    public function list_view(){
        if($this->request->isPost()){
            $this->request->filter(['strip_tags', 'htmlspecialchars']);
            $page      = (int)$this->request->post('page', 0);
            $limit      = (int)$this->request->post('limit', 0);
            $offset = empty($page)?0:($page-1)*$limit;
            $liveItemModel = new LiveItemModel();
            $condition = array();

            $count = $liveItemModel->fetchCount($condition);
            $list  = array();
            if ( 0 < $count ) {
                $list = $liveItemModel->fetchList($condition,$offset,$limit);
            }
            Response::Json(0,"请求成功",$count,$list);
        }
        return $this->view->fetch();
    }

    //添加
    public function add(){
        if($this->request->isPost()){
            $data['item_name'] = trim($this->request->post('item_name'));
            $data['desc'] = trim($this->request->post('desc'));
            $data['status'] = (int)$this->request->post('status');

            if(mb_strlen($data['item_name'],'utf-8')>10){
                $this->error('名称不可超过10个汉字');
            }

            $liveItemModel = new LiveItemModel();

            //验证账号是否唯一
            if($liveItemModel->fetchCount(array('item_name'=>$data['item_name']))){
                $this->error('名称已存在');
            }
            $data['create_time'] = time();
            $id = $liveItemModel->insertGetId($data);
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
            $data['item_name'] = trim($this->request->post('item_name'));
            $data['desc'] = trim($this->request->post('desc'));
            $data['status'] = (int)$this->request->post('status');

            if(mb_strlen($data['item_name'],'utf-8')>10){
                $this->error('名称不可超过10个汉字');
            }
            $liveItemModel = new LiveItemModel();
            $res = $liveItemModel->update($data,array('item_id'=>$id));
            if(false === $res){
                $this->error('更新失败');
            }
            $this->success('更新成功');
        }
        $liveItemModel = new LiveItemModel();
        $info = $liveItemModel->getDetail(array('item_id'=>$id));
        $this->assign('info',$info);
        return $this->view->fetch();
    }

    //添加子项目
    public function detail(){
        $id = (int)$this->request->param('id');
        $liveModel = new LiveModel();
        $liveItemModel = new LiveItemModel();
        $liveItem = $liveItemModel->fetchList(array('status'=>1),0,10);
        $info = $liveModel->getDetail(array('id'=>$id));
        $this->assign('liveItem',$liveItem);
        $this->assign('info',$info);
        $this->assign('id',$id);
        return $this->view->fetch();
    }
}