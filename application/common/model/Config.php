<?php
namespace app\common\model;
class Config extends Base
{
    protected $name = 'config';
    public function getConfigById($id){
        $id = intval($id);
        if (empty($id)) {
            return array();
        }
        $list = $this->where(array('id' => $id))->find();
        if (empty($list)) {
            return array();
        }

        return $list->toArray();
    }
}