<?php
/**
 * [商品推荐]
 */
namespace app\common\model;
class GoodsRecom extends Base{
    protected $name = 'goods_recom';

    protected $type = array(
        'start_time'=>'timestamp',
        'end_time'=>'timestamp'
    );

    const LUN_BO   = 1;//商品轮播
    const TUI_JIAN = 2;//推荐位
    const ALL_BOTH = 3;//双设置



    //获取数量
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
            ->order('a.id desc')
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