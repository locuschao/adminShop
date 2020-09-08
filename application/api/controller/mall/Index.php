<?php
namespace app\api\controller\mall;
use app\common\service\WxPay;
use think\Cache;
use think\Config;


class Index {
  public function index(){
      $redis = Cache::store('redis')->handler();
      $redis->hset('goods','name','a');
      $redis->hset('goods','age','18');
      print_r($redis->hget('goods','name'));die;
      print_r($redis->hgetall('goods'));die;
      $redis->hmset('goods',array('name'=>'a','age'=>18));
      print_r($redis->hmGet('goods', array('name', 'age')));die;

      print_r(Config::get('appid'));die;
      echo time();die;
  }

  public function test(){
      $wx = new WxPay();
      $outRefundNo = "dh".makeOrderid();
      $outTradeNo = "4200000490202002292576760255";
      $totalFee = 1;
      $refundFee = 1;
      $refundDesc = "测试退款";
      $payParams = $wx -> refund($outRefundNo,$outTradeNo,$totalFee,$refundFee,$refundDesc);
      print_r($payParams);die;
  }

  public function a(){
      $params = "123456";
      \think\Hook::listen('app_init',$params);//参数为变量(下同)
  }
}