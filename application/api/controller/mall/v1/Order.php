<?php
/**
 * [订单管理]
 */
namespace app\api\controller\mall\v1;
use app\api\controller\mall\Base;
use app\common\model\GoodsOrder;
use app\common\model\PayOrder;
use app\common\model\UserCoupon;


class Order extends Base{

    const TIMER = 60 * 30;

    //订单详情页
    public function detail(){
        if (empty($this->userInfo)) {
            $this->_error('TOKEN_TIME_OUT');
        }
        $order_id = $this->_param('order_id');
        $user_id = $this->userInfo['id'];
        $payOrderModel = new PayOrder();
        $where = array();
        $where['a.order_sn'] = $order_id;
        $where['a.user_id'] = $user_id;
        $payOrder = $payOrderModel->getUserOrderDetailByCondition($where);

        if(!empty($payOrder)){
            $payOrder['timer'] = intval(strtotime($payOrder['create_time']))+self::TIMER-intval(time())>0?intval(strtotime($payOrder['create_time']))+self::TIMER-intval(time()):0;
        }

        $this->_response['data']['list'] = $payOrder;
        $this->_success('v1.order:detail');
    }

    //等待付款的订单详情(废除)
    public function waitOrderDetail(){
        if (empty($this->userInfo)) {
            $this->_error('TOKEN_TIME_OUT');
        }
        $order_id = $this->_param('order_id');
        $user_id = $this->userInfo['id'];

        $payOrderModel = new PayOrder();
        $where = array();
        $where['a.order_sn'] = $order_id;
        $where['a.user_id'] = $user_id;
        $payOrder = $payOrderModel->getUserOrderDetailByCondition($where);

        if(!empty($payOrder)){
            $payOrder['timer'] = intval(strtotime($payOrder['create_time']))+self::TIMER-intval(time());
        }

        $this->_response['data']['list'] = $payOrder;
        $this->_success('v1.order:waitOrderDetail');
    }


    //订单详情(废除)
    public function finishOrderDetail(){
        if (empty($this->userInfo)) {
            $this->_error('TOKEN_TIME_OUT');
        }
        $order_id = $this->_param('order_id');
        $user_id = $this->userInfo['id'];

        $payOrderModel = new PayOrder();
        $where = array();
        $where['a.order_sn'] = $order_id;
        $where['a.user_id'] = $user_id;
        $payOrder = $payOrderModel->getUserOrderDetailByCondition($where);

        $this->_response['data']['list'] = $payOrder;
        $this->_success('v1.order:finishOrderDetail');
    }

    //取消订单
    public function closeOrder(){
        if (empty($this->userInfo)) {
            $this->_error('TOKEN_TIME_OUT');
        }
        $order_sn = $this->_param('order_id');
        $user_id = $this->userInfo['id'];

        $payOrderModel = new PayOrder();
        $where = array();
        $where['status'] = 0;
        $where['order_sn'] = $order_sn;
        $payOrder = $payOrderModel->getUserOrderListByCondition($where);
        if (empty($payOrder)) {
            $this->_error('CLOSE_ORDER_ERROR');
        }
        $data = array(
            'order_status'=>2,
            'remark'=>"取消订单"
        );
        $res = $payOrderModel->update($data,array('user_id'=>$user_id,'order_sn'=>$order_sn,'order_status'=>0));
        if(false === $res){
            $this->_error('CLOSE_ORDER_ERROR');
        }
        //释放优惠券
        if($payOrder['user_coupon_id']>0){
            $userCouponModel = new UserCoupon();
            $userCouponModel->update(array('is_use_type'=>null,'status'=>0,'use_time'=>null),array('id'=>$payOrder['user_coupon_id']));
        }
        $this->_success('v1.order:closeOrder');
    }


    //删除订单
    public function deleteOrder(){
        if (empty($this->userInfo)) {
            $this->_error('TOKEN_TIME_OUT');
        }
        $id = $this->_param('id');
        $user_id = $this->userInfo['id'];

        $goodsOrderModel = new GoodsOrder();
        $data = array(
            'status'=>-1
        );
        $res = $goodsOrderModel->update($data,array('user_id'=>$user_id,'id'=>$id));
        if(false === $res){
            $this->_error('CLOSE_ORDER_ERROR');
        }
        $this->_success('v1.order:deleteOrder');
    }


    //确认收货
    public function confirmOrder(){
        if (empty($this->userInfo)) {
            $this->_error('TOKEN_TIME_OUT');
        }
        $order_id = $this->_param('order_id');
        $user_id = $this->userInfo['id'];

        $payOrderModel = new PayOrder();

        $data = array(
            'order_status'=>5,
        );

        $res = $payOrderModel->update($data,array('user_id'=>$user_id,'order_sn'=>$order_id,'order_status'=>4));

        if(false === $res){
            $this->_error('CLOSE_ORDER_ERROR');
        }
        $this->_success('v1.order:confirmOrder');
    }



    /**
     * 根据不同的类型获取订单列表
     * @Author: wuyh
     * @Date: 2020/3/22 18:14
     */
    public function getList()
    {
        if(empty($this->userInfo)) return $this->_error('TOKEN_TIME_OUT');
        $params = [
            'user_id' => $this->userInfo['id'],
            'status' => $this->_param('status'),
            'page' => $this->_param('page'),
            'limit' => $this->_param('limit')
        ];

        $payOrder = new PayOrder();
        $data  = $payOrder->getListFromApi($params);
        $this->_response['data']['count'] =  $data['count'];
        $this->_response['data']['list'] = $data['data'];
        $this->_success('v1.Mall.order:getList');
    }
}