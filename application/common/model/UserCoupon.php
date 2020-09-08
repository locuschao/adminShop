<?php
/**
 * [优惠券]
 */
namespace app\common\model;
class UserCoupon extends Base{
    protected $name = 'user_coupon';

    protected $type = [
        'get_time'  =>  'timestamp',
        'use_time'  =>  'timestamp',
        'start_time'  =>  'timestamp',
        'expire_time'  =>  'timestamp',
    ];

    const BUY_TYPE = 1;//购物消费
    const LIVE_TYPE = 2;//直播互动
    const ADMIN_TYPE = 3;//后台发放
    const SHARE_TYPE = 4;//分享获取

    //获取方式
    static $GET_TYPE = array(
        self::BUY_TYPE    => '消费获取',
        self::LIVE_TYPE   => '直播互动',
        self::ADMIN_TYPE  => '后台发放',
        self::SHARE_TYPE  => '分享获取',
    );

    const STATUS_WAIT = 0; //未使用
    const STATUS_USED = 1; //已使用
    const STATUS_EXPIRE = 2;//过期

    static $consume_type = array(
        '1'=>'商品消费',
        '2'=>'红包提现',
    );

    const USE_TYPE_CONSUME = 1; //消费抵扣
    const USE_TYPE_WITHDRAW = 2; //提现

    //获取当前最大id
    public function getMax(){
        return $this->max('id');
    }
    //获取数量
    public function fetchCount(array $condition) {
        $count = $this
            ->where($condition)
            ->count();
        return $count;
    }

    //获取列表
    public function fetchList(array $condition, $offset,$limit = 10) {
        $list = $this
            ->where($condition)
            ->order('id desc')
            ->limit("$offset,$limit")
            ->select();
        return empty($list)?array():$list->toArray();
    }

    //获取详情
    public function getDetail(array $condition,$field="*"){
        $list = $this->where($condition)->field($field)->find();
        return empty($list)?array():$list->toArray();
    }


    /**
     * 关联优惠券/红包
     * @return \think\model\relation\HasOne
     * @Author: wuyh
     * @Date: 2020/3/25 12:26
     */
    public function Coupon()
    {
        return $this->hasOne('Coupon', 'id', 'coupon_id');
    }

    //添加优惠
    public function addCoupon($get,$coupon_id,$user_id,$day,$remark,$live_id=0){
        $time = time();
        $couponModel = new Coupon();
        $coupon = $couponModel->getDetail(array('id'=>$coupon_id));
        $data = array(
            'get'  => $get,
            'remark' => $remark,
            'coupon_id' => $coupon_id,
            'json_data' => json_encode($coupon,true),
            'live_id' => $live_id,
            'coupon_sn' => generateCode(3,4),
            'user_id' => $user_id,
            'get_time' => $time,
            'start_time' => $time,
            'expire_time' => $time+$day*24*3600,
            'insertymd' => date('Ymd',$time),
        );
        return $this->insertGetId($data);
    }
}