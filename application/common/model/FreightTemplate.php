<?php
namespace app\common\model;
class FreightTemplate extends Base{
    protected $name = 'freight_template';

    public function fetchCount(array $condition) {
        $count = $this->where($condition)->count();

        return $count;
    }

    public function fetchList(array $condition, $offset,$limit = 10) {
        $list = $this->where($condition)->order('template_id desc')->limit("$offset,$limit")->select();
        if(empty($list)){
            return array();
        }

        return $list->toArray();
    }

    public function getFreightTemplateInfoByTemplateId($template_id){
        $template_id = intval($template_id);
        if(empty($template_id)){
            return array();
        }
        $list = $this->where(array('template_id'=>$template_id))->find();

        return empty($list)?array():$list->toArray();
    }

    public function getAllFreightTemplate(){
        $list = $this->where(array())->select();

        return empty($list)?array():$list->toArray();
    }
}