<?php
namespace app\common\model;
class Shipping extends Base{
    protected $name = 'shipping';

    public function fetchCount(array $condition) {
        $count = $this->where($condition)->count();

        return $count;
    }

    public function fetchList(array $condition, $offset,$limit = 10) {
        $list = $this->where($condition)->order('shipping_id desc')->limit("$offset,$limit")->select();
        if(empty($list)){
            return array();
        }

        return $list->toArray();
    }

    public function getDetail(array $condition){
        $list = $this->where($condition)->find();
        if(empty($list)){
            return array();
        }
        return $list->toArray();
    }
}
