<?php
namespace app\common\model;
class FreightConfig extends Base{
    protected $name = 'freight_config';

    public function getFreightTemplateConfig($template_id){
        $template_id = intval($template_id);
        if(empty($template_id)){
            return array();
        }
        $list = $this->where(array('template_id'=>$template_id))->select();

        return empty($list)?array():$list->toArray();
    }

    public function getMaxId(){
        $list = $this->field("max(config_id) as id")->order("config_id desc")->find();

        return empty($list)?1:$list->toArray()['id']+1;
    }
}
