<?php
// +----------------------------------------------------------------------
// | 订单服务层
// +----------------------------------------------------------------------
// | Copyright (c) 2019-2020 rights reserved.
// +----------------------------------------------------------------------
// | Author: wuyh
// +----------------------------------------------------------------------
// | Date: 2020/3/23 17:34
// +----------------------------------------------------------------------
namespace app\common\service;

use app\common\model\PayOrder;

class OrderService extends BaseService
{
    /**
     * 获取用户不同类型的订单数量
     * @param $userId
     * @return array
     * @Author: wuyh
     * @Date: 2020/3/23 18:03
     */
    public function getOrderCountByUser($userId)
    {
        $orderModel = new PayOrder();

        $data = [
            'wait_pay' => '0',
            'wait_send' => '0',
            'wait_receive' => '0',
            'all_completed' => '0',

        ];

        if (empty($userId)) return $data;

        //待付款
        $data['wait_pay'] = $orderModel->orderCount('WAIT_PAY', $userId);

        //待发货
        $data['wait_send'] = $orderModel->orderCount('WAIT_SEND', $userId);

        //待收货
        $data['wait_receive'] = $orderModel->orderCount('WAIT_RECEIVE', $userId);

        //已完成
        $data['all_completed'] = $orderModel->orderCount('ALL_COMPLETED', $userId);



        return $data;
    }
}
