<?php
//商品图片
namespace app\common\model;
class GoodsImage extends Base
{
    // 表名
    protected $name = 'goods_image';
    protected $resultSetType = 'collection';

    //获取数量
    public function fetchCount(array $condition) {
        $count = $this->where($condition)->count();
        return $count;
    }

    //获取多条
    public function fetchList(array $condition, $offset,$limit = 10) {
        $list = $this->where($condition)->order('goods_id desc')->limit("$offset,$limit")->select();
        if(empty($list)){
            return array();
        }

        return $list->toArray();
    }

    //获取详情
    public function getDetail(array $condition){
        $list = $this->where($condition)->find();
        if(empty($list)){
            return array();
        }
        return $list->toArray();
    }

    //删除
    public function drop($where){
        return $this->where($where)->delete();
    }
}