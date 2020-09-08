<?php
namespace app\common\model;
class Spec extends Base
{
    // 表名
    protected $name = 'spec';

    //获取数量
    public function fetchCount(array $condition) {
        $count = $this->where($condition)->count();
        return $count;
    }

    //获取多条
    public function fetchList(array $condition, $offset,$limit = 10) {
        $list = $this->where($condition)->order("id asc")->limit("$offset,$limit")->select();
        return empty($list)?array():$list->toArray();
    }

    //获取详情
    public function getDetail(array $condition,$field=""){
        $field = empty($field)?"*":$field;
        $list = $this->where($condition)->field($field)->find();
        return empty($list)?array():$list->toArray();
    }
}