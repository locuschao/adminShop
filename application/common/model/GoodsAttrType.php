<?php
// +----------------------------------------------------------------------
// | 商品模型
// +----------------------------------------------------------------------
// | Author: pc 2020-02-06
// +----------------------------------------------------------------------
namespace app\common\model;
class GoodsAttrType extends Base{
    // 表名
    protected $name = 'goods_type';

    //获取数量
    public function fetchCount(array $condition) {
        $count = $this->where($condition)->count();
        return $count;
    }

    //获取列表
    public function fetchList(array $condition, $offset,$limit = 10) {
        $list = $this->where($condition)->order('id desc')->limit("$offset,$limit")->select();
        return empty($list)?array():$list->toArray();
    }

    //获取单个详情
    public function getDetail(array $condition){
        $list = $this->where($condition)->find();
        return empty($list)?array():$list->toArray();
    }

}

