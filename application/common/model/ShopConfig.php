<?php
//公司配置表
namespace app\common\model;
class ShopConfig extends Base{
    // 表名
    protected $name = 'config';
    public function getDetail(array $condition){
        $navList = $this->where($condition)->find();
        return empty($navList)?array():$navList->toArray();
    }
}