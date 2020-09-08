<?php
/**
 * [商品活动]
 */
namespace app\common\model;
class GoodsActivity extends Base{
    protected $name = 'goods_activity';

    protected $activityField = "a.*,b.type as coupon_type,b.title as coupon_title";

    protected $type = [
        'start_time'  =>  'timestamp',
        'end_time'  =>  'timestamp',
        'create_time'  =>  'timestamp',
    ];

    //获取数量
    public function fetchCount(array $condition) {
        $count = $this
            ->alias('a')
            ->join('cc_coupon b','a.coupon_id=b.id','left')
            ->where($condition)
            ->count();
        return $count;
    }

    //获取列表
    public function fetchList(array $condition, $offset,$limit = 10) {
        $list = $this
            ->alias('a')
            ->field($this->activityField)
            ->join('cc_coupon b','a.coupon_id=b.id','left')
            ->where($condition)
            ->order('a.id desc')
            ->limit("$offset,$limit")
            ->select();
        return empty($list)?array():$list->toArray();
    }

    //获取详情
    public function getDetail(array $condition){
        $list = $this
            ->alias('a')
            ->field($this->activityField)
            ->join('cc_coupon b','a.coupon_id=b.id','left')
            ->where($condition)
            ->find();
        return empty($list)?array():$list->toArray();
    }

}