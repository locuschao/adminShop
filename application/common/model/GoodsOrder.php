<?php
// +----------------------------------------------------------------------
// | 订单商品
// +----------------------------------------------------------------------
// | 修改: Wuyh 2020-03-05
// +----------------------------------------------------------------------
namespace app\common\model;
class GoodsOrder extends Base
{
    protected $name = 'goods_order';

    /**
     * 订单状态
     * @modify wuyh
     * @date 2020-03-05
     */
    const ORDER_STATUS_NORMAL = 1;          //订单状态正常
    const ORDER_STATUS_COMPLETE = 2;        //订单状态完成
    const ORDER_STATUS_CANCEL = 3;          //订单状态取消

    const PAY_STATUS_REFUNDED_NO = 2;       //已退款
    const PAY_STATUS_PARTIAL_NO = 4;        //部分退款
    const PAY_STATUS_REFUNDED = 5;          //已退款

    const SHIP_STATUS_NO = 0;               //未发货
    const SHIP_STATUS_PARTIAL_YES = 1;      //部分发货
    const SHIP_STATUS_YES = 2;              //已发货
    const SHIP_STATUS_PARTIAL_NO = 3;       //部分退货
    const SHIP_STATUS_RETURNED = 4;         //已退货

    const RECEIPT_NOT_CONFIRMED = 1;        //未确认收货
    const CONFIRM_RECEIPT = 2;              //确认收货

    const ALL_PENDING_DELIVERY = 2;         //待发货
    const ALL_PARTIAL_DELIVERY = 8;         //部分发货
    const ALL_PENDING_RECEIPT = 3;          //待收货


    /**
     * 返回table所需要的数据格式
     * @param $params
     * @return mixed
     */
    public function tableData($params)
    {
        if (isset($params['limit'])) {
            $limit = $params['limit'];
        } else {
            $limit = config('cfg.SYS_PAGE');
        }

        $condition = $this->_tableCondition($params);

        $list = $this
            ->with('orders')
            ->alias('g')
            ->join('__PAY_ORDER__ o', 'g.order_sn = o.order_sn', 'LEFT')
            ->field($condition['field'])
            ->where($condition['where'])
            ->order($condition['order'])
            ->paginate($limit);
        $data = $this->_tableFormat($list->getCollection());

        $re['code'] = 0;
        $re['msg'] = '';
        $re['count'] = $list->total();
        $re['data'] = $data;

        return $re;
    }

    /**
     * 格式化表格数据
     * @param $list
     * @return mixed
     * @author wuyh
     */
    private function _tableFormat($list)
    {
        foreach ($list as $k => $v) {
//            if (isset($v['attach']) && empty($v['attach'])) $list[$k]['attach'] = '-';
//            $list[$k]['pay_time'] = isset($v['pay_time']) && $v['pay_time'] > 0 ? date('Y-m-d H:i:s', $v['pay_time']) : '-';
            $list[$k]['ship_status_name'] = shipStatusHtml($v['shipping_status']);
        }
        return $list;
    }

    /**
     * 表格查询条件
     * @param $params
     * @return mixed
     * @author wuyh
     */
    private function _tableCondition($params)
    {
        $map = [];
        if (isset($params['is_send']) && $params['is_send'] != "") $map['o.shipping_status'] = $params['is_send'];
        if (isset($params['status']) && $params['status'] != "") $map['o.status'] = $params['status'];
        if (isset($params['sch_type']) && !empty($params['sch_type']) && isset($params['keyword']) && !empty($params['keyword'])) {
            switch ($params['sch_type']) {
                case 1:
                    $map['g.order_sn'] = $params['keyword'];
                    break;
                case 2:
                    $map['g.goods_name'] = ['like', $params['keyword'] . '%'];
                    break;
                case 3:
                    $map['g.goods_sn'] = ['like', $params['keyword'] . '%'];
                    break;
                case 4:
                    $map['g.id'] = $params['keyword'];
                    break;
            }
        }

        $result['where'] = $map;
        $result['field'] = "g.*, o.status as pay_status,o.order_sn,o.shipping_status";
        $result['order'] = 'create_time desc';

        return $result;
    }


    /**
     * 关联商品
     * @return \think\model\relation\HasOne
     */
    public function goods()
    {
        return $this->hasOne('Goods', 'goods_id', 'goods_id');
    }

    /**
     * 直接关联图片
     * @Author: wuyh
     * @Date: 2020/4/1 11:40
     */
    public function images()
    {
        return $this->hasMany('GoodsImage', 'goods_id', 'goods_id');
    }

    /**
     * 关联订单
     * @return \think\model\relation\HasOne
     */
    public function orders()
    {
        return $this->hasOne('PayOrder','order_sn', 'order_sn')
            ->bind([
                'shipping_id' => 'shipping_id',
                'shipping_name'=>'shipping_name',
                'shipping_time'=>'shipping_time',
                'goods_amount' => 'goods_price',
                'order_amount' => 'order_amount',
                'amount' => 'amount'
            ]);
    }

    /**
     * 批量发货
     * @param $params
     * @return array
     */
    public function batchShipping($params)
    {
        foreach($params['data'] as $v) {
            $this->where(['order_id' => $v['order_id']])->update([
                'delivery_id' => $v['delivery_id'],
                'is_send' => self::SHIP_STATUS_YES,
                'shipping_time' => time()
            ]);

            $shippingInfo = Shipping::where(['shipping_name' => $v['shipping_name']])->find();
            if ($shippingInfo){
                $res = PayOrder::where(['order_sn' => $v['order_id']])->update([
                    'shipping_id' => $shippingInfo->shipping_id,
                    'shipping_name'=> $shippingInfo->shipping_name,
                ]);
            }
        }

        return ['code' => 0, 'msg' => '批量成功'];
    }



}
