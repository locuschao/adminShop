<?php
namespace app\common\model;
class GoodsAttribute extends Base
{
    // 表名
    protected $name = 'goods_attribute';

    //获取数量
    public function fetchCount(array $condition) {
        $count = $this->where($condition)->count();
        return $count;
    }

    //获取多条
    public function fetchList(array $condition, $offset,$limit = 10) {
        $list = $this->where($condition)->order('attr_id desc')->limit("$offset,$limit")->select();
        return empty($list)?array():$list->toArray();
    }

    //获取详情
    public function getDetail(array $condition,$field=""){
        $field = empty($field)?"*":$field;
        $list = $this->where($condition)->field($field)->find();
        return empty($list)?array():$list->toArray();
    }
}