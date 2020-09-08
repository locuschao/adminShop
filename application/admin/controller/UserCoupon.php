<?php
namespace app\admin\controller;
use app\common\model\Coupon as CouponModel;
use library\Auth;
use library\Response;
use app\common\model\UserCoupon as UserCouponModel;
class UserCoupon extends Base
{

    //列表
    public function list_view()
    {
        if ($this->request->isPost()) {
            $this->request->filter(['strip_tags', 'htmlspecialchars']);

            $type = $this->request->post('type');
            $status = $this->request->post('status');
            $page = (int)$this->request->post('page', 0);
            $limit = (int)$this->request->post('limit', 0);
            $offset = empty($page) ? 0 : ($page - 1) * $limit;
            $userCouponModelModel = new UserCouponModel();
            $condition = array();
            if (isset($status) && $status >= 0) {
                $condition['status'] = $status;
            }
            if (isset($type) && $type >= 0) {
                $condition['type'] = $type;
            }
            $count = $userCouponModelModel->fetchCount($condition);
            $list = array();
            if (0 < $count) {
                $list = $userCouponModelModel->fetchList($condition, $offset, $limit);
            }
            Response::Json(0, "请求成功", $count, $list);
        }
        return $this->view->fetch();
    }

    //发放优惠卷
    public function push(){
        $id = $this->request->param('id');
        $couponModel = new CouponModel();
        $userCouponModelModel = new UserCouponModel();
        $info = $couponModel->getDetail(array('id'=>$id));
        if($this->request->isPost()){
            $content = trim($this->request->param('content'));
            if(empty($content)){
                $this->error('请输入用户id');
            }
            if($info['status']==0){
                $this->error('请先启用');
            }
            $user_ids = explode(PHP_EOL,$content);
            $user_ids = array_unique($user_ids);
            $time = time();
            $admin = Auth::instance()->getAdminInfo();
            $data = array();
            foreach ($user_ids as $key =>$value){
                $t = array();
                $t['get'] = $userCouponModelModel::ADMIN_TYPE;
                $t['remark'] = $admin['account']; //操作账号
                $t['coupon_id'] = $id;
                $t['json_data'] = json_encode($info,true);
                $t['coupon_sn'] = generateCode(3,4);
                $t['user_id'] = $value;
                $t['get_time'] = $time;
                $t['start_time'] = $time;
                $t['expire_time'] = $time+$info['day']*24*60*60;
                $t['insertymd'] = date('Ymd',$time);
                $data[] = $t;
            }
            $add = $userCouponModelModel->insertAll($data);
            if(!$add){
                $this->error('发放失败');
            }
            $this->success('发放成功');
        }
        $this->assign('info',$info);
        return $this->view->fetch();
    }

}