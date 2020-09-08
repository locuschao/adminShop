<?php
// +----------------------------------------------------------------------
// | 支付服务层
// +----------------------------------------------------------------------
namespace app\common\service;

class PayService{

    protected $user_id = 0;
    protected $payOrders = array();

    protected $goodsOrders = array();

    protected $payNotify = array();

    /**
     * 创建订单
     * @param   $user_id
     * @param   $payOrderParam
     * @param   $error
     * @return  boolean
     */
    public function createOrder($user_id, array $payOrderParam, &$error = null){

          if(empty($user_id)){
              $error = "用户信息不存在";
              return false;
          }
          $this->user_id = $user_id;
          if(empty($payOrderParam)){
              $error = "支付参数为空";
              return false;
          }

          foreach ($payOrderParam as $value) {
          }
    }


}
