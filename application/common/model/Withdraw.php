<?php
// +----------------------------------------------------------------------
// | 提现模型
// +----------------------------------------------------------------------
// | Copyright (c) 2019-2020 rights reserved.
// +----------------------------------------------------------------------
// | Author: wuyh
// +----------------------------------------------------------------------
// | Date: 2020/3/24 16:51
// +----------------------------------------------------------------------

namespace app\common\model;

use think\Model;

class Withdraw extends Model
{
    protected $name = 'withdraw';
//    protected $resultSetType = 'collection';
    protected $autoWriteTimestamp = true;
    protected $createTime = 'create_at';
    protected $updateTime = 'update_at';

    //提现状态
    const WITHDRAW_STATUS_WAIT_AUDIT = 0; //待审核
    const WITHDRAW_STATUS_WAIT = 1; //审核通过，待结算
    const WITHDRAW_STATUS_ON_WAY = 2; //在途
    const WITHDRAW_STATUS_WAIT_PAY = 3; //已结算
    const WITHDRAW_STATUS_REJECT = 4; //驳回


    const WITHDRAW_TYPE_CASH = 1; //余额
    const WITHDRAW_TYPE_REDPACK = 2;  //红包


    /**
     * 返回table所需要的数据格式
     * @param $params
     * @return mixed
     * @author wuyh
     */
    public function tableData($params)
    {
        $limit = isset($params['limit']) ? $params['limit'] : config('cfg.SYS_PAGE');
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
     * 表格查询条件
     * @param $params
     * @return mixed
     * @author wuyh
     */
    private function _tableCondition($params)
    {
        $map = [
            'withdraw_type' => self::WITHDRAW_TYPE_CASH //默认余额
        ];

        if (isset($params['withdraw_type']) && $params['withdraw_type'] != "") $map['withdraw_type'] = $params['withdraw_type'];
        if (isset($params['status']) && $params['status'] != '') $map['status'] = $params['status'];

        if (!empty($params['create_at'])) {
            $dateStr = $params['create_at'];
            $dateArr = explode(' 至 ', urldecode($dateStr));
            $sdate = strtotime($dateArr[0] . ' 00:00:00');
            $edate = strtotime($dateArr[1] . ' 23:59:59');
            $map['create_at'] = ['between', [$sdate, $edate]];
        }

        if (!empty($params['arrival_time'])) {
            $dateStr = $params['arrival_time'];
            $dateArr = explode(' 至 ', urldecode($dateStr));
            $sdate = strtotime($dateArr[0] . ' 00:00:00');
            $edate = strtotime($dateArr[1] . ' 23:59:59');
            $map['arrival_time'] = ['between', [$sdate, $edate]];
        }

        if (isset($params['sch_type']) && !empty($params['sch_type']) && isset($params['keyword']) && !empty($params['keyword'])){
            switch ($params['sch_type']) {
                case 1:
                    $map['withdraw_no'] = $params['keyword'];
                    break;
                case 2:
                    $map['three_order_sn'] = $params['keyword'];
                    break;
                case 3:
                    $ids = User::where(['nickname' => ['like', $params['keyword'] . '%']])->column('id');
                    $ids= empty($ids) ? [] : implode(',', $ids);
                    $map['user_id'] = ['in', $ids];
                    break;
                case 4:
                    $ids = User::where(['mobile' => $params['keyword']])->column('id');
                    $ids= empty($ids) ? '' : implode(',', $ids);
                    $map['user_id'] = ['in', $ids];
                    break;
            }
        }

        $result['where'] = $map;
        $result['field'] = "*";
        $result['order'] = 'create_at desc';
        return $result;
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
        }
        return $list;
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
     * 关联管理员
     * @return \think\model\relation\HasOne
     * @Author: wuyh
     * @Date: 2020/3/26 10:10
     */
    public function admin()
    {
        return $this->hasOne('AdminUser', 'id', 'operator_id')->bind([
            'admin_name' => 'name',
        ]);
    }
}