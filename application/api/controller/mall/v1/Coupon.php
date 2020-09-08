<?php
/**
 * [优惠券]
 */
namespace app\api\controller\mall\v1;
use app\api\controller\mall\Base;
use app\common\service\CouponService;

class Coupon extends Base{

      //获取用户优惠券
      public function ableCoupon(){
          if(empty($this->userInfo)) {
              $this->_error('TOKEN_TIME_OUT');
          }
          $amount =  floatval($this->_param('amount'));
          $type = (int)$this->_param('type');
          $page = (int)$this->_param('page');
          $limit = (int)$this->_param('limit');
          if(empty($type)||empty($amount)){
              $this->_error('PARAM_ERROR');
          }
          $couponService = new CouponService();
          $param = array();
          $param['user_id'] = $this->userInfo['id'];
          $param['status'] = 0;
          $param['type'] = $type;
          $param['full_money'] = $amount;
          $param['time'] = time();
          $param['page'] = empty($page)?1:$page;
          $param['limit'] = empty($limit)?10:$limit;
          $field = "uc.id, c.title,c.full_money,c.money,c.type c_type";
          $list = $couponService ->getUserCouponList($param,$field);
          $this->_response['data']['list'] = $list;
          $this->_success('v1.coupon:ableCoupon');
      }


     //赠送优惠券
     public function giveCoupon(){
         if (empty($this->userInfo)) {
             $this->_error('TOKEN_TIME_OUT');
         }
         $order_id = $this->_param('order_id');
         $user_id = $this->userInfo['id'];

         $couponService = new CouponService();
         $data = $couponService ->getUserCouponByOrderSn(array('order_sn'=>$order_id,'user_id'=>$user_id));
         $list = array();
         if(!empty($data)){
            $list = json_decode($data['json_data'],true);
         }
         $this->_response['data']['list'] = $list;
         $this->_success('v1.coupon:giveCoupon');
     }



}