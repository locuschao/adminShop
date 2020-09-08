<?php
namespace app\common\model;
class AdminRole extends Base{
    // 表名
    protected $name = 'admin_role';

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


    public function getRoleList($condition){
        $list = $this->where($condition)->select();
        if(empty($list)){
            return array();
        }
        return $list->toArray();
    }


    public function getRoleInfoById($id){
        $id = intval($id);
        if($id<=0){
            return array();
        }
        $menu = $this->where(array('id'=>$id))->find();

        if(empty($menu)){
            return array();
        }
        $menu = $menu->toArray();
        return $menu;
    }



}
