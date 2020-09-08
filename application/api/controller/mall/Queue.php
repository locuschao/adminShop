<?php
/**
 * [队列]
 */
namespace app\api\controller\mall;
use app\common\model\PayOrder;
use app\common\model\UserCoupon;
use app\common\service\DelayQueueService;
use think\Config;
class Queue {

    //关闭订单
    public function index(){
        $close_key = "close_order";
        $queue = new DelayQueueService($close_key);
        $payOrderModel = new PayOrder();
        $userCouponModel = new UserCoupon();
        while ($task = $queue->getTask()){
            $task = $task[0];
            //有并发的可能，这里通过zrem返回值判断谁抢到该任务
            if ($queue->delTask($task)) {
                $task = json_decode($task, true);
                //处理任务
                $params = $task['task_params'];
                $order_id = $params['order_sn'];
                $pay = $payOrderModel->where(array('order_sn' => $order_id, 'status' => 0))->find();
                $pay = empty($pay)?array():$pay->toArray();
                //查询订单状态
                if(empty($pay)){
                    log_message('队列处理订单号:'.$order_id, 'log', Config::get('log_dir').'/mall/queue_close_order/'); continue;
                }

                //更新订单状态
                $payOrderModel->update(array('status'=>1,'order_status' => 2,'remark'=>"超时未支付"),array('order_sn'=>$order_id,'status'=>0));

                //更新优惠券
                $userCouponModel->update(array('is_use_type'=>null,'status'=>0,'use_time'=>null),array('id'=>$pay['user_coupon_id']));

            }
        }
    }
    
}