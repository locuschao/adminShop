<?php
/**
 * [券票列表]
 * @author pc
 */
namespace app\admin\controller;
use library\Response;
use library\XunSearch;
use app\common\model\Coupon as CouponModel;
use app\common\model\UserCoupon as UserCouponModel;
class Coupon extends Base{

    //列表
    public function list_view(){
        if($this->request->isPost()){
            $this->request->filter(['strip_tags', 'htmlspecialchars']);
            $xunSearch = new XunSearch();
            $keywords   = $this->request->post('keywords');
            $ids = $xunSearch ->searchCoupon($keywords);
            $type   = $this->request->post('type');
            $status   = $this->request->post('status');
            $page      = (int)$this->request->post('page', 0);
            $limit      = (int)$this->request->post('limit', 0);
            $offset = empty($page)?0:($page-1)*$limit;
            $couponModel = new CouponModel();
            $condition = array();
            if(!empty($keywords)){
                $condition['id'] = array('in',$ids);
            }
            if(isset($status) && $status>=0){
                $condition['status'] = $status;
            }
            if(isset($type) && $type>=0){
                $condition['type'] = $type;
            }
            $count = $couponModel->fetchCount($condition);
            $list  = array();
            if ( 0 < $count ) {
                $list = $couponModel->fetchList($condition,$offset,$limit);
            }
            Response::Json(0,"请求成功",$count,$list);
        }
        return $this->view->fetch();
    }

    //添加
    public function add(){
        if($this->request->isPost()){
            $data['title'] = trim($this->request->post('title'));
            $data['type'] = (int)$this->request->post('type');
            $data['day'] = (int)$this->request->post('day');
            $data['is_withdraw'] = (int)$this->request->post('is_withdraw');
            $data['status'] = (int)$this->request->post('status');
            $data['full_money'] = (int)$this->request->post('full_money');
            $data['money'] = (int)$this->request->post('money');
            $data['create_time'] = time();

            $validate = new \app\common\validate\coupon();
            $result = $validate->scene('save')->check($data);
            if(!$result){
                $this->error($validate->getError());
            }
            $couponModel = new CouponModel();
            $id = $couponModel->insertGetId($data);
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
            $couponModel = new CouponModel();
            $res = $couponModel->update($data,array('id'=>$id));
            if(false === $res){
                $this->error('更新失败');
            }
            $this->success('更新成功');
        }
        $couponModel = new CouponModel();
        $info = $couponModel->getDetail(array('id'=>$id));
        $this->assign('info',$info);
        return $this->view->fetch();
    }

    //批量处理
    public function batch_check(){
        if($this->request->isPost()){
            $ids = $this->request->param('ids/a');
            $status = (int)$this->request->param('check');
            $couponModel = new CouponModel();
            $res = $couponModel->update(array('status'=>$status),array('id'=>array('in',$ids)));
            if(false === $res){
                $this->error('更新失败');
            }
            $this->success('更新成功');
        }
        return $this->view->fetch();
    }

    //ajax获取优惠券
    public function ajaxCouponList(){
        if($this->request->isPost()){
            $type = $this->request->param('type');
            $couponModel = new CouponModel();
            $list = $couponModel->fetchList(array('type'=>$type,'status'=>1),0,100);
            if(empty($list)){
                $this->error('没有优惠活动');
            }
            $this->success('请求成功','',$list);
        }
    }

    //子券明细
    public function coupon_detail(){
        $id = $this->request->param('id');
        if($this->request->isPost()){
            $this->request->filter(['strip_tags', 'htmlspecialchars']);
            $is_use_type   = $this->request->post('is_use_type');
            $get   = $this->request->post('get');
            $status   = $this->request->post('status');
            $page      = (int)$this->request->post('page', 0);
            $limit      = (int)$this->request->post('limit', 0);
            $offset = empty($page)?0:($page-1)*$limit;
            $userCouponModel = new UserCouponModel();
            $condition = array();
            if(isset($is_use_type) && $is_use_type>=0){
                $condition['is_use_type'] = $is_use_type;
            }
            if(isset($get) &&  $get>=0){
                $condition['get'] = $get;
            }
            if(isset($status) && $status>=0){
                switch ($status){
                    case 0:
                        $condition['status'] = $status;
                        break;
                    case 1:
                        $condition['status'] = $status;
                        break;
                    case 2:
                        $condition['expire_time'] = array('<',time());
                        break;
                }
            }
            if(isset($id) && $id>=0){
                $condition['coupon_id'] = $id;
            }
            $count = $userCouponModel->fetchCount($condition);
            $list  = array();
            if ( 0 < $count ) {
                $list = $userCouponModel->fetchList($condition,$offset,$limit);
                foreach ($list as &$value){
                    if(strtotime($value['expire_time'])<time()){
                        $value['status'] = 2;
                    }
                    $value['get'] = UserCouponModel::$GET_TYPE[$value['get']];
                    $value['is_use_type'] = empty($value['is_use_type'])?"-":UserCouponModel::$consume_type[$value['is_use_type']];
                }
            }
            Response::Json(0,"请求成功",$count,$list);
        }
        $this->assign('get_style',UserCouponModel::$GET_TYPE);
        $this->assign('id',$id);
        return $this->view->fetch();
    }
}