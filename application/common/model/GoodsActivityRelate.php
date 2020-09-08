<?php
/**
 * [商品关联活动]
 */
namespace app\common\model;
class GoodsActivityRelate extends Base{
    protected $name = 'goods_activity_relate';

    public function fetchCount(array $condition) {
        $count = $this
            ->alias('a')
            ->join('cc_goods b','a.goods_id=b.goods_id','left')
            ->where($condition)
            ->count();
        return $count;
    }

    //获取列表
    public function fetchList(array $condition, $offset,$limit = 10) {
        $list = $this
            ->alias('a')
            ->join('cc_goods b','a.goods_id=b.goods_id','left')
            ->where($condition)
            ->order('a.goods_id desc')
            ->limit("$offset,$limit")
            ->select();
        return empty($list)?array():$list->toArray();
    }

    //获取详情
    public function getDetail(array $condition){
        $list = $this
            ->where($condition)
            ->find();
        return empty($list)?array():$list->toArray();
    }

    //查询满足条件的商品
    public function getActivityGoodsList(array $condition) {
        $list = $this
            ->where($condition)
            ->select();
        return empty($list)?array():$list->toArray();
    }


    //查询满足条件的活动
    public function getActivityListByCondition(array $condition){
        $list = $this
            ->alias('a')
            ->field('a.goods_id,a.start_time,a.end_time,b.coupon_id,c.title,c.type,c.full_money,c.money,c.day')
            ->join('cc_goods_activity b','a.goods_activity_id=b.id','left')
            ->join('cc_coupon c','c.id=b.coupon_id','left')
            ->where($condition)
            ->find();
        return empty($list)?array():$list->toArray();
    }

}