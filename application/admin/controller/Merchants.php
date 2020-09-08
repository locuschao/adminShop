<?php
/**
 * [商家管理]
 * @author pc
 */
namespace app\admin\controller;
use library\Response;
use library\XunSearch;
use app\common\model\Merchants as MerchantsModel;
use app\common\model\MerchantsExt as MerchantsExtModel;
class Merchants extends Base{

    //列表
    public function list_view(){
        if($this->request->isPost()){
            $this->request->filter(['strip_tags', 'htmlspecialchars']);
            $xunSearch = new XunSearch();
            $keywords   = $this->request->post('keywords');
            $ids = $xunSearch ->searchMerchants($keywords);
            $status   = $this->request->post('status');
            $page      = (int)$this->request->post('page', 0);
            $limit      = (int)$this->request->post('limit', 0);
            $offset = empty($page)?0:($page-1)*$limit;
            $merchantsModel = new MerchantsModel();
            $condition = array();
            if(!empty($keywords)){
                $condition['id'] = array('in',$ids);
            }
            if(isset($status) && $status>=0){
                $condition['status'] = $status;
            }

            $count = $merchantsModel->fetchCount($condition);
            $list  = array();
            if ( 0 < $count ) {
                $list = $merchantsModel->fetchList($condition,$offset,$limit);
            }
            Response::Json(0,"请求成功",$count,$list);
        }
        return $this->view->fetch();
    }

    //添加
    public function add(){
        if($this->request->isPost()){
            $data['platform'] = trim($this->request->post('platform'));
            $data['username'] = trim($this->request->post('username'));
            $data['password'] = trim($this->request->post('password'));
            $data['contact'] = trim($this->request->post('contact'));
            $data['responser'] = trim($this->request->post('responser'));
            $data['mobile'] = trim($this->request->post('mobile'));
            $data['weixin'] = trim($this->request->post('weixin'));

            $validate = new \app\common\validate\Merchants();
            $result = $validate->scene('save')->check($data);
            if(!$result){
                $this->error($validate->getError());
            }

            if(!check_username($data['username'])){
                $this->error('用户账号只能是数字或者字母，或者组合');
            }

            //验证电话号码
            if(strlen($data['username'])<6 || strlen($data['username'])>18){
                $this->error('用户名账号只能在6到18位');
            }

            //验证电话号码
            if(!check_phone($data['mobile'])){
                $this->error('电话号码格式错误');
            }

            $merchantsModel = new MerchantsModel();
            //验证账号是否唯一
            if($merchantsModel->fetchCount(array('username'=>$data['username']))){
                $this->error('账号已存在');
            }
            $data['password'] = autoCreatePassword($data['password']);
            $time = time();
            $data['create_time'] = $time;
            $data['insertymd'] =  date('Ymd',$time);
            $id = $merchantsModel->insertGetId($data);
            if($id<=0){
                $this->error('添加失败');
            }
            //插入扩展表
            $merchantsExtModel = new MerchantsExtModel();
            $merchantsExtModel->insert(array('merchants_id'=>$id));
            $this->success('添加成功');
        }
        return $this->view->fetch();
    }

    //编辑
    public function edit(){
        $id = (int)$this->request->param('id');
        if($this->request->isPost()){
            $data['platform'] = trim($this->request->post('platform'));
            $data['contact'] = trim($this->request->post('contact'));
            $data['responser'] = trim($this->request->post('responser'));
            $data['mobile'] = trim($this->request->post('mobile'));
            $data['weixin'] = trim($this->request->post('weixin'));
            //验证电话号码
            if(!check_phone($data['mobile'])){
                $this->error('电话号码格式错误');
            }
            $validate = new \app\common\validate\Merchants();
            $result = $validate->scene('save')->check($data);
            if(!$result){
                $this->error($validate->getError());
            }
            $merchantsModel = new MerchantsModel();
            $data['update_time'] = time();
            $res = $merchantsModel->update($data,array('id'=>$id));
            if(false === $res){
                $this->error('更新失败');
            }
            $this->success('更新成功');
        }
        $merchantsModel = new MerchantsModel();
        $info = $merchantsModel->getDetail(array('id'=>$id));
        $this->assign('info',$info);
        return $this->view->fetch();
    }

    //批量处理
    public function batch_check(){
        if($this->request->isPost()){
            $ids = $this->request->param('ids/a');
            $status = (int)$this->request->param('check');
            $merchantsModel = new MerchantsModel();
            $res = $merchantsModel->update(array('status'=>$status),array('id'=>array('in',$ids)));
            if(false === $res){
                $this->error('更新失败');
            }
            $this->success('更新成功');
        }
        return $this->view->fetch();
    }

}