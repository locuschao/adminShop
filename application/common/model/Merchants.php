<?php
/**
 * [商家]
 */
namespace app\common\model;
class Merchants extends Base{
    protected $name = 'merchants';
    protected $autoWriteTimestamp = true;
    protected $createTime = 'create_time';

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