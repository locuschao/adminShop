<?php
// +----------------------------------------------------------------------
// | 订单模型
// +----------------------------------------------------------------------
// | 修改: Wuyh 2020-03-05
// +----------------------------------------------------------------------
namespace app\common\model;

class PayOrder extends Base
{
    protected $name = 'pay_order';

    //保护字段
    protected $pay_field = "a.parent_sn,a.order_sn as order_id,a.consignee,a.mobile,a.address,a.shipping_id,a.goods_price,a.shipping_price,a.goods_price,a.order_amount,a.total_amount,a.create_time,a.pay_time,a.status,a.user_coupon_id,a.coupon_amount,a.remark,a.order_status,b.item_id,b.goods_id,b.goods_name,b.spec_key_name,b.goods_price as goods_price_unit,b.goods_num,c.src as image";


    /**
     * 订单状态
     * @date 2020-03-20 （调整）
     * @author wuyh
     */

    const PAY_STATUS_NO = 0;                //未付款
    const PAY_STATUS_FAIL = 1;              //支付失败
    const PAY_STATUS_YES = 99;              //已付款

    const SHIP_STATUS_NO = 0;               //未发货
    const SHIP_STATUS_PARTIAL_YES = 1;      //部分发货
    const SHIP_STATUS_YES = 2;              //已发货
    const SHIP_STATUS_PARTIAL_NO = 3;       //部分退货
    const SHIP_STATUS_RETURNED = 4;         //已退货

    const NO_COMMENT = 1;                   //没有评价
    const ALREADY_COMMENT = 2;              //已经评价

    const WAIT_PAY = 0;                     //订单状态待支付
    const ORDER_CANCEL = 2;                 //订单状态已取消
    const WAIT_SEND = 3;                    //订单状态待发货
    const WAIT_RECEIVE = 4;                 //订单状态已发货
    const ALL_COMPLETED = 5;                //订单状态已完成

    const REFUND_STATUS_NO = 0;    //售后状态无售后
    const REFUND_STATUS_ING = 1;    //售后状态进行中
    const REFUND_STATUS_FINISH = 2;    //售后状态完成
    const REFUND_STATUS_CANCLE = 3;    //售后状态取消



    /**
     * 返回table所需要的数据格式
     * @param $params
     * @return mixed
     * @author wuyh
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
            ->with(['user'])
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
            if (isset($v['attach']) && empty($v['attach'])) $list[$k]['attach'] = '-';
            $list[$k]['pay_time'] = isset($v['pay_time']) && $v['pay_time'] > 0 ? date('Y-m-d H:i:s', $v['pay_time']) : '-';
            $list[$k]['paytype_name'] = isset(config('enum.order_pay')['type'][$v['paytype']]) ? config('enum.order_pay')['type'][$v['paytype']] : '未知';
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

        if (isset($params['paytype']) && $params['paytype'] != "") $map['paytype'] = $params['paytype'];
        if (isset($params['status']) && $params['status'] !== "") $map['status'] = $params['status'];

        if (!empty($params['create_time'])) {
            $dateStr = $params['create_time'];
            $dateArr = explode(' 至 ', urldecode($dateStr));
            $sdate = strtotime($dateArr[0] . ' 00:00:00');
            $edate = strtotime($dateArr[1] . ' 23:59:59');
            $map['create_time'] = ['between', [$sdate, $edate]];
        }

        if (!empty($params['pay_time'])) {
            $dateStr = $params['pay_time'];
            $dateArr = explode(' 至 ', urldecode($dateStr));
            $sdate = strtotime($dateArr[0] . ' 00:00:00');
            $edate = strtotime($dateArr[1] . ' 23:59:59');
            $map['pay_time'] = ['between', [$sdate, $edate]];
        }

        if (isset($params['sch_type']) && !empty($params['sch_type']) && isset($params['keyword']) && !empty($params['keyword'])) {
            switch ($params['sch_type']) {
                case 1:
                    $map['order_sn'] = $params['keyword'];
                    break;
                case 2:
                    $map['attach'] = $params['keyword'];
                    break;
                case 3:
                    $map['consignee'] = ['like', $params['keyword'] . '%'];
                    break;
                case 4:
                    $map['address'] = ['like', $params['keyword'] . '%'];
                    break;
                case 5:
                    $map['mobile'] = $params['keyword'];
                    break;
            }
        }

        $result['where'] = $map;
        $result['field'] = "*";
        $result['order'] = 'create_time desc';
        return $result;
    }

    /**
     * 关联用户
     * @return \think\model\relation\HasOne
     * @author  wuyh
     */
    public function user()
    {
        return $this->hasOne('User', 'id', 'user_id');
    }

    /**
     * 关联订单商品
     * @return \think\model\relation\HasMany
     * @author wuyh
     */
    public function orderGoods()
    {
        return $this->hasMany('GoodsOrder', 'order_sn', 'order_sn');
    }


    //获取订单详情
    public function getUserOrderDetailByCondition($where)
    {
        $order = $this
            ->alias('a')
            ->field($this->pay_field)
            ->where($where)
            ->join('cc_goods_order b', 'a.order_sn=b.order_sn', 'left')
            ->join('cc_goods_image c', 'b.goods_id=c.goods_id', 'left')
            ->find();
        return empty($order) ? array() : $order->toArray();
    }

    //获取订单
    public function getUserOrderListByCondition($where)
    {
        $order = $this->where($where)->find();
        return empty($order) ? array() : $order->toArray();
    }


    /**
     * 接口使用 - 获取不同状态的订单
     * @param $params
     * @param bool $isPage 是否分页
     * @return mixed
     * @Author: wuyh
     * @Date: 2020/3/22 18:01
     */
    public function getListFromApi($params, $isPage = true)
    {
        $map = $this->getOrderStatus($params['status']);
        $map['user_id'] = $params['user_id'];
        $page = $params['page'] ? $params['page'] : 1;
        $limit = $params['limit'] ? $params['limit'] : config('cfg.ORDER_PAGE_LIMIT');
        $data = [];
        $count = 0;

        if ($isPage) {
            $data = $this->with('GoodsOrder.Goods.images')->where($map)
                ->order('create_time DESC')
                ->page($page, $limit)
                ->select();
            if ($data){
                //备注 需优化
                $data = $data->toArray();
                foreach ($data as $k => $v){
                    $data[$k]['goods_num'] = 0;
                    $data[$k]['cover_image'] = 0;
                    $data[$k]['goods_name'] = '';
                    if ($v['goods_order']){
                        foreach($v['goods_order'] as $goodsOrder){
                            $data[$k]['goods_name'] = $goodsOrder['goods_name'];
                            $data[$k]['goods_num'] += $goodsOrder['goods_num'];
                            if ($goodsOrder['goods']['images']){
                                foreach ($goodsOrder['goods']['images'] as $image){
                                    if ($image['is_seleted'] == 1){
                                        $data[$k]['cover_image'] = $image['src'];
                                        break;
                                    }
                                }
                            }
                        }
                    }

                    unset($data[$k]['goods_order']);
                }
            }

            $count = $this->where($map)->count();
        }

        return ['data' => $data, 'count' => $count];
    }

    /**
     * 订单状态查询，需组合查询
     * @param $status
     * @return array
     * @Author: wuyh
     * @Date: 2020/3/22 16:26
     */
    protected function getOrderStatus($status)
    {
        $map = [];
        switch ($status) {
            case 'WAIT_PAY': //待付款(目前还未涉及货到付款)
                $map = [
                    'order_status' => self::WAIT_PAY
                ];
                break;
            case 'WAIT_SEND': //待发货
                $map = [
                    'order_status' => self::WAIT_SEND,
                ];
                break;
            case 'ORDER_CANCEL': //取消
                $map = [
                    'order_status' => self::ORDER_CANCEL,
                ];
                break;
            case 'WAIT_RECEIVE': //待收货
                $map = [
                    'order_status' => self::WAIT_RECEIVE
                ];
                break;
            case 'ALL_COMPLETED': //已完成
                $map = [
                    'order_status' => self::ALL_COMPLETED,
                ];
                break;
            default:
                break;
        }

        return $map;
    }

    /**
     * 订单数量
     * @param string $status
     * @param bool $userId
     * @return int|string
     * @Author: wuyh
     * @Date: 2020/3/23 17:56
     */
    public function orderCount($status = '', $userId = false)
    {
        $where = [];
        if ($userId) $where['user_id'] = $userId;

        $where = array_merge($where, $this->getOrderStatus($status));
        return $this->where($where)->count();
    }

    /**
     * 售后关联
     * @author wuyh
     */
    public function Aftersales()
    {
        return $this->hasMany('OrderAftersales', 'order_id', 'id');
    }

    /**
     * 关联商品订单
     * @return \think\model\relation\HasMany
     * @Author: wuyh
     * @Date: 2020/3/25 17:27
     */
    public function GoodsOrder()
    {
        return $this->hasMany('GoodsOrder', 'order_sn', 'order_sn');
    }
}