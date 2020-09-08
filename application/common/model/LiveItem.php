<?php
/**
 * [直播项]
 */
namespace app\common\model;
class LiveItem extends Base{
    protected $name = 'live_item';

    protected $type = [
        'create_time'  =>  'timestamp',
    ];

    //活动类型
    const single = 1;//单次互动
    const view = 2;//观看赠礼
    const draw = 3;//抽奖
    const answer = 4;//问答


    //单次互动
    const SIGN  = 1;//签到互动
    const LIVE  = 2;//直播赠礼
    const SHARE = 3;//分享赠礼
    const BUY   = 4;//购物赠礼

    //单次互动类型
    static $interact_type = array(
        self::SIGN  => '签到互动',
        self::LIVE  => '直播赠礼',
        self::SHARE => '分享赠礼',
        self::BUY   => '购物赠礼',
    );

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
            ->order('item_id desc')
            ->limit("$offset,$limit")
            ->select();
        return empty($list)?array():$list->toArray();
    }

    //获取详情
    public function getDetail(array $condition){
        $list = $this->where($condition)->find();
        return empty($list)?array():$list->toArray();
    }


    //查询单次活动列表
    public function getLiveItemListByCondition(array $condition,$field="*"){
        $list = $this
            ->alias('a')
            ->field($field)
            ->where($condition)
            ->join('cc_live_item_detail b','a.item_id=b.item_id','left')
            ->select();
        return empty($list)?array():$list->toArray();
    }

}