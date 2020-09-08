<?php
namespace app\common\model;
class FreightRegion extends Base{
    protected $name = 'freight_region';

    public function getRegionByTemplateId($template_id){
        $template_id = intval($template_id);
        if(empty($template_id)){
            return array();
        }
        $where = array();
        $where['a.template_id'] = $template_id;
        $list = $this->alias('a')->field('a.config_id,a.region_id,b.name')->join('cc_region b','a.region_id=b.id','left')->where($where)->select();

        return empty($list)?array():$list->toArray();
    }
}