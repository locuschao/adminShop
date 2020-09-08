<?php
/**
 * [支付参数类]
 */
namespace app\common\model;
class PayOrderParam{
    /**
     * 用户id
     * @var int
     */
    public $user_id = 0;

    /**
     * 微信号openid
     * @var int
     */
    public $openid = 0;

    /**
     * 商家id [0是平台自营]
     * @var int
     */
    public $merchants_id = 0;

    /**
     * 收货人
     * @var int
     */
    public $consignee = 0;

    /**
     * 收货地址id
     * @var int
     */
    public $address_id = 0;

    /**
     * 收货人电话
     * @var string
     */
    public $mobile = "";

    /**
     * 物流id
     * @var int
     */
    public $shipping_id = 0;

    /**
     * 运费
     * @var
     */
    public $shipping_price;

    /**
     * 运费模板
     * @var int
     */
    public $template_id = 0;

    /**
     * 支付方式[1微信支付 2混合支付 3优惠券支付 4钱包支付]
     * @var int
     */
    public $pay_style = 1;

    /**
     * 商品总价
     * @var int
     */
    public $goods_price = 0;

    /**
     * 订单金额
     * @var int
     */
    public $order_amount = 0;

    /**
     * 优惠券id
     * @var int
     */
    public $user_coupon_id = 0;

    /**
     * 优惠金额
     * @var int
     */
    public $coupon_amount = 0;

    /**
     * 直播间id
     * @var int
     */
    public $live_id = 0;

    /**
     * 支付ip
     * @var string
     */
    public $ip = '';


}
