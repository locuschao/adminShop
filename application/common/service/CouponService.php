<?php
/**
 * [优惠卷]
 */

namespace app\common\service;

use app\common\model\Coupon;
use app\common\model\GoodsActivityRelate as GoodsActivityRelateModel;
use app\common\model\UserCoupon;
use app\common\model\Withdraw;
use app\common\validate\Withdraw AS WithdrawValidate;
use think\Db;
use think\Exception;

class CouponService extends BaseService
{
    static $ERROR_CODE = [
        '1001' => '暂时不开放红包提现',
        '1002' => '钱包状态异常，请联系客服',
        '1003' => '没有找到红包信息',
        '1004' => '红包已经使用',
        '1005' => '红包已经过期',
        '1006' => '红包不可提现',
        '1007' => '红包状态被禁用',
        '1008' => '红包可提现金额有误',
    ];


    //缓存用户领取优惠
    public function getUserCouponCache($coupon_id, $user_id)
    {
        $cacheKey = md5('getUserCouponCache' . $coupon_id . $user_id);
        $data = $this->redisCache->get($cacheKey);
        if (!empty($data)) {
            return false;
        }
        $this->redisCache->set($cacheKey, $coupon_id);
        return true;
    }

    /**
     * 获取用户卡包数量
     * @param $userId
     * @return int|string
     * @Author: wuyh
     * @Date: 2020/3/25 14:29
     */
    public function getCouponCountByUser($userId)
    {
        $userFollow = new UserCoupon();
        return $userFollow->where(['user_id' => $userId])->count();
    }

    /**获取用户优惠券或红包
     * @param $params
     * @return array
     * @Author: wuyh
     * @Date: 2020/3/25 12:29
     */
    public function getUserCouponList($params,$field=null)
    {
        $res = [
            'count' => 0,
            'list' => [],
            'has_next'=>0
        ];
        if (!isset($params['user_id']) || empty($params['user_id'])) return $res;

        $map = [
            'uc.user_id' => $params['user_id'],
        ];

        if (isset($params['type'])) $map['c.type'] = $params['type'];
        if (isset($params['status'])) $map['uc.status'] = $params['status'];
        if (isset($params['full_money'])) $map['c.full_money'] = array('<=',$params['full_money']);
        if (isset($params['time'])) {
            $map['uc.start_time']  = array('<=',$params['time']);
            $map['uc.expire_time'] = array('>=',$params['time']);
        }

        $page = isset($params['page']) ? $params['page'] : 1;
        $limit = isset($params['limit']) ? $params['limit'] : config('cfg.ORDER_PAGE_LIMIT');
        $userCoupon = new UserCoupon();

        $res['count'] = $userCoupon->alias('uc')
            ->join('__COUPON__ c', 'c.id = uc.coupon_id', 'LEFT')
            ->where($map)
            ->count();

        $field = empty($field)?"uc.*, c.title,c.full_money,c.money,c.is_withdraw,c.status c_status,c.type c_type":$field;

        $list = $userCoupon->alias('uc')
            ->join('__COUPON__ c', 'c.id = uc.coupon_id', 'LEFT')
            ->field($field)
            ->order('uc.insertymd ASC')
            ->page($page, $limit)
            ->where($map)
            ->select();

        if ($list) {
            $res['list'] = $list->toArray();
            $res['has_next'] = $res['count']>$page*$limit?1:0;
            foreach ($res['list'] as &$v) {
                if ($v['c_type'] == 1) {
                    $v['coupon_desc'] = "满{$v['full_money']}减{$v['money']}元";
                } else {

                }
            }
        }

        return $res;
    }

    /**
     * 红包提取申请
     * @param $params
     * @return array
     * @Author: wuyh
     * @Date: 2020/3/26 11:06
     */
    public function redpackWithdraw($params)
    {
        $res = [
            'code' => 0,
            'msg' => '',
            'data' => ''
        ];

        if (2 != config('cfg.REDPACK_WITHDRAW_SWITCH')) {
            $res['msg'] = static::$ERROR_CODE['1001'];
            return $res;
        }

        $couponId = $params['coupon_id'] ?: 0;
        $userId = $params['user_id'] ?: 0;

        if (empty($couponId) || empty($userId)) {
            $res['msg'] = '参数错误';
            return $res;
        }

        $userWalletService = new UserWalletService();

        //检查用钱包的状态
        if ($userWalletService->checkWallet($userId)) {
            $res['msg'] = static::$ERROR_CODE['1002'];
            return $res;
        }

        $couponModel = new UserCoupon();
        $coupon = $couponModel
            ->lock(true)
            ->with('Coupon')
            ->where(['user_id' => $userId, 'coupon_id' => $couponId])
            ->find();

        if (empty($coupon)) {
            $res['msg'] = static::$ERROR_CODE['1003'];
            return $res;
        }
        if ($coupon->status == UserCoupon::STATUS_USED) {
            $res['msg'] = static::$ERROR_CODE['1004'];
            return $res;
        }

        if ($coupon->status == UserCoupon::STATUS_EXPIRE) {
            $res['msg'] = static::$ERROR_CODE['1005'];
            return $res;
        }

        if ($coupon->coupon['is_withdraw'] == Coupon::WITHDRAW_DISABLE) {
            $res['msg'] = static::$ERROR_CODE['1006'];
            return $res;
        }

        if ($coupon->coupon['status'] == Coupon::STATUS_DISABLE) {
            $res['msg'] = static::$ERROR_CODE['1007'];;
            return $res;
        }

        $money = $coupon->coupon['money'];

        if ($money <= 0){
            $res['msg'] = static::$ERROR_CODE['1008'];;
            return $res;
        }

        $data = [
            'user_id' => $userId,
            'money' => $money,
            'arrival_money' => $money,
            'poundage' => 0,
            'withdraw_type' => Withdraw::WITHDRAW_TYPE_REDPACK,
            'withdraw_way' => 4, //提取到余额
            'poundage_ratio' => 0,
            'withdraw_no' => create_no(config('enum.orders_prefix')['RWH'])
        ];
        $withdrawValidate = new WithdrawValidate();
        $msg = $withdrawValidate->scene('withdraw')->check($data);

        if ($msg !== true) {
            $res['msg'] = $withdrawValidate->getError();
            return $res;
        }

        $withdraw = new Withdraw();

        try {
            Db::startTrans();

            //创建提现订单
            $ret = $withdraw->allowField(true)->save($data);
            if ($ret === false) throw new Exception('申请是失败');

            //修改
            $coupon->status = UserCoupon::STATUS_USED;
            $coupon->is_use_type = 2;
            $coupon->use_time = time();
            $ret = $coupon->save();

            if ($ret === false) throw new Exception('申请失败 - 1002');

            Db::commit();
            return ['code' => 1, 'msg' => '申请成功'];
        } catch (Exception $e) {
            Db::rollback();
            return ['code' => 0, 'msg' => $e->getMessage()];
        }
    }

    //获取下单成功获取赠送的优惠福利
    public function getUserCouponByOrderSn($where){
        $userCouponModel = new UserCoupon();
        $list =  $userCouponModel
            ->where($where)
            ->find();
        return empty($list)?array():$list->toArray();
    }


    //购买赠送福利
    public function buyGiveCoupon($goods_id,$user_id,$goods_name){
        $goodsActivityRelateModel = new GoodsActivityRelateModel();
        $CouponService = new CouponService();
        $time = time();
        $where = array();
        $where['a.goods_id'] = $goods_id;
        $where['b.status'] = 1;
        $where['b.type'] = UserCoupon::BUY_TYPE;
        $where['a.start_time'] = array('<=',$time);
        $where['a.end_time'] = array('>=',$time);
        $where['c.status'] = 1;
        $list = $goodsActivityRelateModel -> getActivityListByCondition($where);
        if(!empty($list)){
            if($CouponService->getUserCouponCache($list['coupon_id'],$user_id)) {
                $userCouponModel = new UserCoupon();
                return $userCouponModel->addCoupon(UserCoupon::SHARE_TYPE, $list['coupon_id'], $user_id, $list['day'], $goods_name);
            }
        }
    }

}