<?php
namespace app\api\controller\mall\v1;
use app\common\model\GoodsExt;
use app\common\model\PayOrder;
use app\common\model\PayNotify as PayNotifyModel;
use app\common\model\Goods as GoodsModel;
use app\common\model\SpecGoodsPrice;
use app\common\model\GoodsOrder;
use app\common\service\CouponService;
use app\common\service\LiveActivityService;
use app\common\service\WxPay as WxPayService;
use think\Config;
use think\Db;
use think\Exception;

class Notify {
    /**
     * 微信 支付完成回调处理
     *
     */
    public function wxpay() {

        $request_data = file_get_contents('php://input');

        log_message('微信支付请求数据1:'.$request_data, 'log', Config::get('log_dir').'/mall/pay_Notify_log/');

        if(empty($request_data)){
            $msg  = '请求数据为空';
            $code = 'FAIL1';
            $this->retureXml($code,$msg);
        }

        $wxPayService = new WxPayService();
        $request_data = $wxPayService->xmlToArray($request_data);
        log_message('微信支付请求数据2:'.print_r($request_data), 'log', Config::get('log_dir').'/mall/pay_Notify_log/');
        if(empty($request_data)){
            $msg  = '数据解析失败';
            $code = 'FAIL2';
            $this->retureXml($code,$msg);
        }

        $request_data = array_filter($request_data);

        $soucre_sign  = $request_data['sign']; // 接收到的签名

        log_message('微信支付接受到的签名:'.$soucre_sign, 'log', Config::get('log_dir').'/mall/pay_Notify_log/');

        unset($request_data['sign']);          // 参数中的sign不参与签名

        $sign = $wxPayService->getSign($request_data);


        if(!Config::get('app_debug')){
            //签名验证
            if($soucre_sign != $sign){
                //写入日志
                log_message('微信支付处理的签名:'.$sign, 'log', Config::get('log_dir').'/mall/pay_Notify_log/');
                $msg  = '签名验证失败';
                $code = 'FAIL3';
                $this->retureXml($code,$msg);
            }
        }


        if('SUCCESS' != $request_data['result_code']){
            //写入日志
            log_message('微信支付回调失败:'.print_r($request_data), 'log', Config::get('log_dir').'/mall/pay_Notify_log/');
            $msg  = '签名验证失败';
            $code = 'FAIL3';
            $this->retureXml($code,$msg);
        }

        $parent_sn = $request_data['out_trade_no'];//支付订单号
        $transaction_id = $request_data['transaction_id'];

        //订单编号不为空时
        if(!empty($parent_sn)){
            //更新订单状态处理
            $this->updateOrder($parent_sn,$transaction_id);
        }

        $msg  = '回调成功';
        $code = 'SUCCESS';

        log_message('微信支付回调成功:'.$parent_sn, 'log', Config::get('log_dir').'/mall/pay_Notify_log/');

        $this->retureXml($code,$msg);
    }


    public function retureXml($code,$msg){
        $xml = <<<EOT
    <xml>
        <return_code><![CDATA[%s]]></return_code>
        <return_msg><![CDATA[%s]]></return_msg>
    </xml>
EOT;
        $xml = sprintf($xml, strtoupper($code), $msg);

        exit($xml);
    }


    private function updateOrder($parent_sn,$transaction_id){
        if(empty($parent_sn)){
            return false;
        }

        $payOrderModel = new PayOrder();
        $payNotifyModel = new PayNotifyModel();
        $goodsModel = new GoodsModel();
        $SpecGoodsPrice = new SpecGoodsPrice();
        $goodsExtModel = new GoodsExt();
        $liveActivityService = new LiveActivityService();
        $couponService = new CouponService();

        $payOrder = $payOrderModel->where(array('parent_sn'=>$parent_sn))->select()->toArray();

        Db::startTrans();
        try {

            foreach ($payOrder as $val){

                $payOrderModel->update(array('status'=>99,'pay_time'=>time(),'attach'=>$transaction_id,'order_status'=>3),array('order_sn'=>$val['order_sn'],'status'=>0));

                $payNotifyModel->update(array('status'=>99,'update_time'=>time()),array('order_id'=>$val['order_sn'],'status'=>0));

                $payNotifyOrder = $payNotifyModel->where(array('order_id'=>$val['order_sn']))->find()->toArray();

                $order_info = json_decode($payNotifyOrder['callback_url'],true);

                $user_id = $order_info['user_id'];

                foreach ($order_info['order_info'] as $value){

                    $goodsModel->where(array('goods_id'=>$value['goods_id']))->setDec('store_count',$value['goods_num']);

                    $goodsModel->where(array('goods_id'=>$value['goods_id']))->setInc('sales_sum',$value['goods_num']);

                    $SpecGoodsPrice->where(array('item_id'=>$value['item_id'],'goods_id'=>$value['goods_id']))->setDec('store_count',$value['goods_num']);

                    $goodsExtModel->updateGoodsRealSalesCount($value['goods_id'],$value['goods_num']);

                    //直播购买赠礼
                    if(isset($value['live_id']) && !empty($value['live_id'])){
                        $liveActivityService->liveBuyGive($value['live_id'],$user_id);
                    }

                    if(empty($value['live_id'])){
                        $couponService->buyGiveCoupon($value['goods_id'],$user_id,$value['goods_name']);
                    }
                }
            }


            Db::commit();

        }catch (Exception $e){

            Db::rollback();
        }
    }
}
