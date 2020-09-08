<?php
namespace app\common\model;
class Region extends Base{
    protected $name = 'region';

    public function getAllProvinces(){
        $list = $this->where(array('level'=>1))->select();
        return empty($list)?array():$list->toArray();
    }

    //获取单个详情
    public function getDetail(array $condition){
        $list = $this->where($condition)->find();
        if(empty($list)){
            return array();
        }

        return $list->toArray();
    }

    //获取所有
    public function getAllRegion(){
        $address = $this->select();
        return empty($address)?array():$address->toArray();
    }
}