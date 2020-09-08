<?php
/**
 * [优惠券]
 */
namespace app\common\model;
class Coupon extends Base{
    protected $name = 'coupon';
    protected $autoWriteTimestamp = true;
    protected $createTime = 'create_time';

    /**
     * @date 2020-03-25
     * @author 2uyh
     */
    const STATUS_DISABLE = 0; //禁用
    const STATUS_ENABLE = 1; //启用
    const WITHDRAW_DISABLE = 0; //不可提现
    const WITHDRAW_ENABLE = 1; //可提现

    //获取数量
    public function fetchCount(array $condition) {
        $count = $this
            ->where($condition)
            ->count();
        return $count;
    }

    //获取列表
    public function fetchList(array $condition, $offset,$limit = 10) {
        $list = $this
            ->where($condition)
            ->order('id desc')
            ->limit("$offset,$limit")
            ->select();
        return empty($list)?array():$list->toArray();
    }

    //获取详情
    public function getDetail(array $condition){
        $list = $this->where($condition)->find();
        return empty($list)?array():$list->toArray();
    }
}