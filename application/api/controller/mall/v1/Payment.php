<?php
/**
 * [测试类支付-兼容拆单合单支付]
 */
namespace app\api\controller\mall\v1;
use app\api\controller\mall\Base;
use app\common\model\Goods as GoodsModel;
use app\common\model\GoodsExt;
use app\common\model\GoodsOrder;
use app\common\model\PayNotify;
use app\common\model\PayOrder;
use app\common\model\SpecGoodsPrice;
use app\common\model\UserAddress;
use app\common\service\CommonService;
use app\common\service\DelayQueueService;
use app\common\service\WxPay;
use app\common\model\UserCoupon;
use think\Db;
use think\Config;

class Payment extends Base {

    private $mch_id;//商户号id
    private $wx_key;//微信key
    private $notify_url;//回调地址

    const TIMER = 60 * 30;
    const PAY_LIMIT_KEY = 'pay_limit_'; // 支付限制缓存前缀

    protected $body = "微娱电商小程序-商品支付";

    public function __construct()
    {
        parent::__construct();
        $this->mch_id = Config::get('mch_id');
        $this->wx_key = Config::get('wx_key');
        $this->notify_url = Config::get('notify_url');
        if (empty($this->mch_id) || empty($this->wx_key) || empty($this->notify_url)) {
            $this->_error('MCH_PAY_ERROR');
        }
    }

    public function pay(){
        if (empty($this->userInfo)) {
            $this->_error('TOKEN_TIME_OUT');
        }
        $user_id = $this->userInfo['id'];
        $openid = $this->userInfo['openid'];
        $pay_data = $this->_param('pay_data');
        if(empty($pay_data)){
            $this->_error('PAY_PARAM_ERROR');
        }

        $amount = $this->_param('amount');//实际支付金额
        if(empty($amount)){
            $this->_error('PAY_AMOUNT_ERROR');
        }
        $goodsModel = new GoodsModel();
        $specGoodsPriceModel = new SpecGoodsPrice();
        $userAddress = new UserAddress();
        $payOrderModel = new PayOrder();

        $payOrders = array();
        $goodsOrders = array();
        $payNotifyOrders = array();
        $order_sns = array();//订单集合
        $user_coupon_ids = array();

        $time = time();
        $total_order_amount = 0;//总金额
        $detail="商品详情:";
        $i = 1;
        foreach ($pay_data as $key=>$value){
            $order_sn = makeOrderid();
            $order_sns[] = $order_sn;
            $payOrder = array();
            $goodsOrder = array();
            $payOrder['order_sn'] = $order_sn;
            $payOrder['merchants_id'] = $value['merchants_id'];
            $payOrder['user_id'] = $user_id;

            //用户地址兼容多个地址
            $address = $userAddress->getDetail(array('user_id' => $user_id, 'id' => $value['address_id']));

            $payOrder['consignee'] = $address['consignee'];
            $payOrder['province'] = $address['province'];
            $payOrder['city'] = $address['city'];
            $payOrder['area'] = $address['area'];
            $payOrder['town'] = $address['town'];
            $payOrder['address'] = $address['address'];
            $payOrder['mobile'] = $address['mobile'];

            //物流现在默认平台选择物流
            $payOrder['shipping_id'] = $value['shipping_id'];
            $payOrder['shipping_name'] = '普通快递';
            $payOrder['shipping_price'] = $value['shipping_price'];
            $payOrder['template_id'] = $value['template_id'];

            $goods_total_price = 0;
            foreach ($value['goods'] as $kk=>$item){

                $goods = $goodsModel->getDetail(array('goods_id' => $item['goods_id']));
                if (empty($goods)) {
                    $this->_error('NOT_GOODS');
                }
                $spec_key = '';
                $spec_key_name = '';
                $goods_price = $goods['shop_price'];

                $goods_num = $goods['store_count'];
                if (!empty($item['item_id'])) {
                    //查询sku
                    $goodsSku = $specGoodsPriceModel->getGoodsSkuItemByItemId($item['goods_id'], $item['item_id']);
                    if (empty($goodsSku)) {
                        $this->_error('GOODS_SKU_EMPTY');
                    }
                    $spec_key = $goodsSku['key'];
                    $spec_key_name = $goodsSku['key_name'];
                    $goods_price = $goodsSku['price'];
                    $goods_num = $goodsSku['store_count'];
                }

                if ($item['goods_num'] > $goods_num) {
                    $this->_error('STORE_COUNT_ERROR');
                }
                $detail .= "（".($i)."）".mb_substr($goods['goods_name'],0,6,'utf-8');
                $detail .="|".$spec_key_name;

                $goodsOrder['order_sn'] = $order_sn;
                $goodsOrder['goods_id'] = $item['goods_id'];
                $goodsOrder['live_id'] = $item['live_id'];
                $goodsOrder['user_id'] = $user_id;
                $goodsOrder['goods_name'] = $goods['goods_name'];
                $goodsOrder['goods_sn'] = $goods['goods_sn'];
                $goodsOrder['goods_num'] = $item['goods_num'];
                $goodsOrder['market_price'] = $goods['market_price'];
                $goodsOrder['goods_price'] = $goods_price;
                $goodsOrder['cost_price'] = $goods['cost_price'];
                $goodsOrder['item_id'] = $item['item_id'];
                $goodsOrder['spec_key'] = $spec_key;
                $goodsOrder['spec_key_name'] = $spec_key_name;
                $goodsOrder['create_time'] = $time;

                $goodsOrders[] = $goodsOrder;

                $goods_total_price += bcmul($goods_price, $item['goods_num'], 2);
                $i++;
            }

            $total_amount = bcadd($goods_total_price, $value['shipping_price'], 2);
            $order_amount = bcsub($total_amount, $value['coupon_amount'], 2)>0?bcsub($total_amount, $value['coupon_amount'], 2):0;
            $total_order_amount += $order_amount;
            //支付方式
            $pay_type = 1;
            if ($order_amount==0) {
                $pay_type = 3;
            }
            if($order_amount>0 && $value['coupon_amount']>0){
                $pay_type = 2;
            }
            $payOrder['goods_price'] = $goods_total_price;//商品总价
            $payOrder['order_amount'] = $order_amount;//商品总价+运费-优惠
            $payOrder['total_amount'] = $total_amount;//商品总价+运费
            $payOrder['amount'] = $order_amount;//实际支付
            $payOrder['paytype'] = $pay_type;
            $payOrder['status'] = 0;
            $payOrder['create_time'] = $time;
            $payOrder['ip'] = request()->ip();
            $payOrder['insertymd'] = date("Ymd", $time);
            $payOrder['user_coupon_id'] = $value['user_coupon_id'];
            $payOrder['coupon_amount'] = $value['coupon_amount'];

            $payOrders[] = $payOrder;

            //通知数据组合
            $param = array(
                'user_id' => $user_id,
                'order_info' => $goodsOrders,
            );

            $payNotify['order_id'] = $order_sn;
            $payNotify['callback_url'] = json_encode($param, true);
            $payNotify['create_time'] = time();
            $payNotify['status'] = 0;

            $payNotifyOrders[] = $payNotify;

            if($value['user_coupon_id']){
                $user_coupon_ids[] = $value['user_coupon_id'];
            }

        }

        //判断支付金额
        if($total_order_amount !=$amount){
            $this->_error('PAY_AMOUNT_ERROR');
        }

        //父订单号
        $parent_sn = 'WY'.makeOrderid();

        // 判断5s内是否重复下单,解决支付并发问题
        $cacheKey = self::PAY_LIMIT_KEY . $user_id;
        $commonService = new CommonService();
        if (!$commonService->requestDuplicateCheck($cacheKey, 5)) {
            $this->_error('REQUEST_TOO_MUCH');
        }


        //下单
        if (!($this->createOrder($payOrders,$goodsOrders,$payNotifyOrders))) {
            $this->_error('ORDER_ERROR');
        }

        //0元支付成功
        if($amount==0){

            if(!self::zeroPay($parent_sn,$order_sns,$user_coupon_ids)){
                $this->_error('ORDER_ERROR');
            }

            $this->_response['data']['order_id'] = $parent_sn;
            $this->_response['data']['payParams'] = array();
            $this->_response['data']['pay_status'] = 99;
            $this->_success('v1.order:pay');
        }

        //入延迟队列
        self::delayQueue($order_sns);

        //调微信支付
        $wxPayService = new WxPay();
        $payParams = $wxPayService->pay($this->body, $detail, $parent_sn, $amount, $openid, $error);
        if (empty($payParams)) {
            $payOrderModel = new PayOrder();
            $payOrderModel->update(array('status' => 1, 'pay_time' => time()), array('parent_sn' => $parent_sn, 'status' => 0));
            $this->_error('PAY_PARAM_ERROR', $error);
        }

        //更新优惠券
        if($user_coupon_ids){
            $userCouponModel = new UserCoupon();
            $userCouponModel->update(array('is_use_type'=>1,'status'=>1,'use_time'=>time()),array('id'=>array('in',$user_coupon_ids)));
        }

        //更新父订单号
        if(empty($order_sns)){
            $this->_error('PAY_PARAM_ERROR');
        }

        $result = $payOrderModel->update(array('parent_sn'=>$parent_sn),array('order_sn'=>array('in',$order_sns),'status'=>0,'order_status'=>0));
        if(!$result){
            $this->_error('PAY_PARAM_ERROR');
        }

        $this->_response['data']['order_id'] = $parent_sn;
        $this->_response['data']['payParams'] = $payParams;
        $this->_response['data']['pay_status'] = 0;
        $this->_success('v1.order:pay');
    }


    //创建订单
    private function createOrder($payOrders,$goodsOrders,$payNotifyOrders)
    {
        $payOrderModel = new PayOrder();
        $goodsOrderModel = new GoodsOrder();
        $payNotifyModel = new PayNotify();

        Db::startTrans();
        $payId = $payOrderModel->insertAll($payOrders);

        if (!$payId) {
            Db::rollback();
            return false;
        }

        $goods_Order_id = $goodsOrderModel->insertAll($goodsOrders);
        if (!$goods_Order_id) {
            Db::rollback();
            return false;
        }

        $payNotifyId = $payNotifyModel->insertAll($payNotifyOrders);

        if (!$payNotifyId) {
            Db::rollback();
            return false;
        }
        Db::commit();
        return true;
    }


    //重新支付
    public function repay(){
        $request_data = file_get_contents('php://input');
        if (empty($this->userInfo)) {
            $this->_error('TOKEN_TIME_OUT');
        }
        $user_id = $this->userInfo['id'];
        $openid = $this->userInfo['openid'];
        log_message('重新支付['.$user_id.']:'.$request_data, 'log', Config::get('log_dir').'/mall/repay/');
        //是否重新支付
        $order_sn = $this->_param('order_id');
        if(empty($order_sn)){
            $this->_error('PAY_PARAM_ERROR');
        }

        $payOrderModel = new PayOrder();
        $goodsOrder = new GoodsOrder();

        //查询订单状态
        $where = array();
        $where['status'] = 0;
        $where['order_sn'] = $order_sn;
        $order = $payOrderModel->getUserOrderListByCondition($where);

        if (empty($order)) {
            $this->_error('PAY_ORDER_ERROR');
        }

        //查询关联的商品信息
        $goodsList = $goodsOrder->where(array('order_sn'=>$order_sn))->select()->toArray();
        $detail="商品详情:";
        $i = 1;

        $goods_total_price = 0;
        foreach ($goodsList as $value){
            $detail .= "（".($i)."）".mb_substr($value['goods_name'],0,6,'utf-8');
            $detail .="|".$value['spec_key_name'];
            //检测商品
            $this->checkGoodsStore($value['goods_id'],$value['item_id'],$value['goods_num']);
            $goods_total_price += bcmul($value['goods_price'], $value['goods_num'], 2);
            $i++;
        }
        $total_amount = bcadd($goods_total_price, $order['shipping_price'], 2);
        $order_amount = bcsub($total_amount, $order['coupon_amount'], 2)>0?bcsub($total_amount, $order['coupon_amount'], 2):0;

        if($order['order_amount'] !=$order_amount){
            $this->_error('PAY_AMOUNT_ERROR');
        }

        //父订单
        $parent_sn = 'WY'.makeOrderid();

        $wxPayService = new WxPay();
        $payParams = $wxPayService->pay($this->body, $detail, $order_sn, $order_amount, $openid, $error);
        if (empty($payParams)) {
            $payOrderModel->update(array('status' => 1, 'pay_time' => time()), array('order_sn' => $order_sn, 'status' => 0));
            $this->_error('PAY_PARAM_ERROR', $error);
        }
        //更新父订单号
        $result = $payOrderModel->update(array('parent_sn'=>$parent_sn),array('order_sn'=>$order_sn,'status'=>0,'order_status'=>0));
        if(!$result){
            $this->_error('PAY_PARAM_ERROR');
        }

        $this->_response['data']['order_id'] = $parent_sn;
        $this->_response['data']['payParams'] = $payParams;
        $this->_response['data']['pay_status'] = 0;
        $this->_success('v1.order:repay');
    }

    //检查商品
    private function checkGoodsStore($goods_id,$item_id,$order_goods_num)
    {
        $goodsModel = new GoodsModel();
        $specGoodsPriceModel = new SpecGoodsPrice();

        $goods = $goodsModel->getDetail(array('goods_id' => $goods_id));
        if (empty($goods)) {
            $this->_error('NOT_GOODS');
        }

        $goods_num = $goods['store_count'];
        if (!empty($item_id)) {
            $goodsSku = $specGoodsPriceModel->getGoodsSkuItemByItemId($goods_id, $item_id);
            if (empty($goodsSku)) {
                $this->_error('GOODS_SKU_EMPTY');
            }
            $goods_num = $goodsSku['store_count'];
        }

        if ($order_goods_num > $goods_num) {
            $this->_error('STORE_COUNT_ERROR');
        }
    }

    //入延迟队列
    private static function delayQueue($order_sns){

        $close_key = "close_order";

        $delayQueueService = new DelayQueueService($close_key);

        foreach ($order_sns as $order_sn){
            $delayQueueService->addTask("close_order_" . $order_sn, time() + self::TIMER, array('order_sn' => $order_sn));
        }
    }

    //0元支付情况
    private static function zeroPay($parent_sn,$order_sns,$user_coupon_ids){
        $payOrderModel = new PayOrder();
        $goodsOrderModel = new GoodsOrder();
        $payNotifyModel = new PayNotify();
        $goodsModel = new \app\common\model\Goods();
        $SpecGoodsPrice = new SpecGoodsPrice();
        $goodsExtModel = new GoodsExt();
        Db::startTrans();

        //修改状态
        $resPay = $payOrderModel->update(array('parent_sn'=>$parent_sn,'pay_time'=>time(),'paytype'=>3,'status'=>99,'order_status'=>3),array('order_sn'=>array('in',$order_sns),'status'=>0,'order_status'=>0));
        if(!$resPay){
            Db::rollback();
            return false;
        }

        //查询商品订单
        $goodsOrderList = $goodsOrderModel->where(array('order_sn'=>array('in',$order_sns)))->select();
        if(empty($goodsOrderList)){
            Db::rollback();
            return false;
        }

        //修改库存
        $goodsOrderList=$goodsOrderList->toArray();
        foreach ($goodsOrderList as $value){
            $goodsModel->where(array('goods_id'=>$value['goods_id']))->setDec('store_count',$value['goods_num']);

            $goodsModel->where(array('goods_id'=>$value['goods_id']))->setInc('sales_sum',$value['goods_num']);

            $SpecGoodsPrice->where(array('item_id'=>$value['item_id'],'goods_id'=>$value['goods_id']))->setDec('store_count',$value['goods_num']);

            $goodsExtModel->updateGoodsRealSalesCount($value['goods_id'],$value['goods_num']);
        }

        //更新支付通知表
        $payNotifyRes = $payNotifyModel->update(array('status'=>99),array('order_id'=>array('in',$order_sns)));
        if(!$payNotifyRes){
            Db::rollback();
            return false;
        }

        //更新优惠券
        if($user_coupon_ids){
            $userCouponModel = new UserCoupon();
            $userCouponModel->update(array('is_use_type'=>1,'status'=>1,'use_time'=>time()),array('id'=>array('in',$user_coupon_ids)));
        }

        Db::commit();
        return true;
    }
}