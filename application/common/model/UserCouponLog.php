<?php
/**
 * [优惠券记录表]
 */
namespace app\common\model;
class UserCouponLog extends Base{
    protected $name = 'user_coupon_log';

    protected $type = [
        'insert_time'  =>  'timestamp',
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

    //来源
    const ARTICLE = 1;
    const SHOP = 2;
    const LIVE = 3;

    //来源
    static $from = array(
        self::ARTICLE=>'文章',
        self::SHOP=>'商城',
        self::LIVE=>'直播'
    );


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
     * @param $from[来源]
     * @param $get[获取方式]
     * @param $coupon_id[优惠券id]
     * @param $user_id[用户id]
     * @param $ext[扩展字段]
     * @return int|string
     */
    public function addCouponLog($from,$get,$coupon_id,$user_id,$ext){
        $time = time();
        $couponModel = new Coupon();
        $coupon = $couponModel->getDetail(array('id'=>$coupon_id));
        $data = array(
            'from' => $from,
            'get'  => $get,
            'coupon_id' => $coupon_id,
            'json_data' => json_encode($coupon,true),
            'user_id' => $user_id,
            'ext' => $ext,
            'insert_time' => $time,
            'insert_ymd' => date('Ymd',$time),
        );
        return $this->insertGetId($data);
    }
}