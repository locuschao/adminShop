<?php
namespace app\common\model;
class AdminMenu extends Base{
    // 表名
    protected $name = 'admin_menu';

    public function fetchCount(array $condition) {
        $count = $this->where($condition)->count();

        return $count;
    }

    public function fetchList(array $condition, $offset,$limit = 10) {
        $list = $this->where($condition)->order('weight desc,id desc')->limit("$offset,$limit")->select();
        if(empty($list)){
            return array();
        }

        return $list->toArray();
    }

    public function getTreeMenu( $where = array()){
        $menu = $this->order('weight desc,id asc')->where($where)->select();
        if(empty($menu)){
            return array();
        }
        $menu = $menu->toArray();
        return $menu;
    }

    public function getMenuInfoById($id){
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



    public function getTree($data, $pId)
    {
        $tree = array();
        foreach($data as $k => $v)
        {
            if($v['pid'] == $pId)
            {         //父亲找到儿子
                $son = $this->getTree($data, $v['id']);
                $v['children'] = empty($son)?array():$son;
                $tree[] = $v;
            }
        }
        return $tree;
    }



}
