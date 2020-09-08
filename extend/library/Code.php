<?php
/**
 * 接口代码说明
 * User: cqingt
 * Date: 2017/12/5
 * Time: 14:14
 */
namespace library;

class Code{
    static public $code = [
        'SUCCESS'                => ['code' => '200', 'msg' => '请求成功'],
        'ERROR_RESPONSE'         => ['code' => '201', 'msg' => '错误的响应输出'],
        'TOKEN_TIME_OUT'         => ['code' => '202', 'msg' => 'Token过期'],
        'UNKNOWN_ERROR'          => ['code' => '400', 'msg' => '未知错误'],
        'PARAM_ERROR'            => ['code' => '401', 'msg' => '参数传递有误'],
        'PARAM_NOT_EMPTY'        => ['code' => '402', 'msg' => '参数不能为空'],
        'SIGN_NOT_MATCH'         => ['code' => '403', 'msg' => '签名错误'],
        'API_NOT_FOUND'          => ['code' => '404', 'msg' => '接口未找到'],
        'DATA_ACTION_ERROR'      => ['code' => '405', 'msg' => '数据操作失败'],
        'VERIFY_CODE_EMPTY'      => ['code' => '406', 'msg' => '验证码不能为空'],
        'DATA_NOT_EXIST'         => ['code' => '407', 'msg' => '没有数据'],
        'DATA_NOT_ADDRESS'       => ['code' => '408', 'msg' => '无效的收货地址'],
        'NOT_GOODS'              => ['code' => '409', 'msg' => '商品不存在'],
        'ORDER_AMOUNT_ERROR'     => ['code' => '410', 'msg' => '订单金额与商品金额不一致'],
        'PAY_AMOUNT_ERROR'       => ['code' => '411', 'msg' => '支付金额有误'],
        'MCH_PAY_ERROR'          => ['code' => '412', 'msg' => '商户支付没配置'],
        'ORDER_ERROR'            => ['code' => '413', 'msg' => '下单失败'],
        'STORE_COUNT_ERROR'      => ['code' => '414', 'msg' => '库存不足'],
        'PAY_PARAM_ERROR'        => ['code' => '415', 'msg' => '支付参数错误'],
        'CLOSE_ORDER_ERROR'      => ['code' => '416', 'msg' => '关闭订单失败'],
        'PAY_ORDER_ERROR'        => ['code' => '417', 'msg' => '订单已支付'],
        'REQUEST_TOO_MUCH'       => ['code' => '418', 'msg' => '订单已存在，请前往我的订单进行支付'],
        'GOODS_SKU_EMPTY'        => ['code' => '419', 'msg' => '没有该商品的SKU'],
        'COUPON_EMPTY'           => ['code' => '420', 'msg' => '优惠不存在'],
        'HAS_COUPON'             => ['code' => '421', 'msg' => '优惠券已经领取过'],
        'COUPON_AMOUNT_MATCH'             => ['code' => '421', 'msg' => '优惠金额不匹配'],
        'SINGLE_ACTIVITY'        => ['code' => '422', 'msg' => '没领取到福利'],
        'invalid appid'          => ['code' => '40029', 'msg' => 'code 无效'],

    ];

    /**
     * @param $key
     * @param string $errorMsg
     * @return array|mixed
     */
    public static function get($key, $errorMsg = '')
    {
        if (! empty($errorMsg)) {
            return array_merge(! empty(self::$code[$key]) ? self::$code[$key] : self::$code['UNKNOWN_ERROR'], ['msg' => $errorMsg]);
        } else {
            return ! empty(self::$code[$key]) ? self::$code[$key] : [];
        }
    }
}
