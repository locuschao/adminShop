<?php
/**
 * 商品活动
 */
namespace app\api\controller\mall\v1;
use app\api\controller\mall\Base;
use app\common\model\GoodsActivityRelate as GoodsActivityRelateModel;
use app\common\model\UserCoupon;
use app\common\model\Coupon as CouponModel;
use app\common\service\CouponService;
use app\common\model\Goods as GoodModel;
class Activity extends Base{

    //获取商品优惠券
    public function goodsDetailActivity(){
        $goods_id =  $this->_param('goods_id');
        $user_id = empty($this->userInfo)?0:$this->userInfo['id'];
        $goodsActivityRelateModel = new GoodsActivityRelateModel();
        $userCouponModel = new UserCoupon();
        $time = time();
        $where = array();
        $where['a.goods_id'] = $goods_id;
        $where['b.status'] = 1;
        $where['b.type'] = UserCoupon::SHARE_TYPE;
        $where['a.start_time'] = array('<=',$time);
        $where['a.end_time'] = array('>=',$time);
        $where['c.status'] = 1;
        $list = $goodsActivityRelateModel -> getActivityListByCondition($where);
        if(!empty($list)){
            $userCoupon = $userCouponModel->getDetail(array('user_id'=>$user_id,'coupon_id'=>$list['coupon_id']));
            $list['is_get'] = empty($userCoupon)?0:1;
        }
        $this->_response['data']['list'] = $list;
        $this->_success('v1.activity:goodsDetailActivity');
    }

    //获取优惠券
    public function getCoupon(){
        $coupon_id =  $this->_param('coupon_id');
        $goods_id =  $this->_param('goods_id');
        $user_id = empty($this->userInfo)?0:$this->userInfo['id'];
        if(empty($user_id)){
            $this->_error('TOKEN_TIME_OUT');
        }
        $couponModel = new CouponModel();
        $coupons = $couponModel->getDetail(array('id'=>$coupon_id,'status'=>1));
        if(empty($coupons)){
            $this->_error('COUPON_EMPTY');
        }
        $goodsModel = new GoodModel();
        $goods = $goodsModel->getDetail(array('goods_id'=>$goods_id),'goods_name');
        if(empty($goods)){
            $this->_error('NOT_GOODS');
        }
        $CouponService = new CouponService();
        if($CouponService->getUserCouponCache($coupon_id,$user_id)){
            $userCouponModel = new UserCoupon();
            $userCouponModel->addCoupon(UserCoupon::SHARE_TYPE,$coupon_id,$user_id,$coupons['day'],$goods['goods_name']);
            $this->_success();
        }else{
            $this->_error('HAS_COUPON');
        }
    }
}