<?php
namespace app\common\model;
class Nav extends Base{
    protected $name = 'nav_list';
    public function fetchCount(array $condition) {
        $count = $this->where($condition)->count();

        return $count;
    }

    public function fetchList(array $condition, $offset,$limit = 10) {
        $list = $this->where($condition)->order('id desc')->limit("$offset,$limit")->select();
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

    public function getNav(){
        $navList = $this->where(array('is_show'=>1))->order('order_by desc')->select();
        return empty($navList)?array():$navList->toArray();
    }
}