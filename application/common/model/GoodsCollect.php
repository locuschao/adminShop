<?php
// +----------------------------------------------------------------------
// | 商品收藏
// +----------------------------------------------------------------------
// | Copyright (c) 2019-2020 rights reserved.
// +----------------------------------------------------------------------
// | Author: wuyh
// +----------------------------------------------------------------------
// | Date: 2020/3/18 12:00
// +----------------------------------------------------------------------
namespace app\common\model;
use think\Model;

class GoodsCollect extends Model
{
    protected $name = 'goods_collect';
    protected $resultSetType = 'collection';
    protected $autoWriteTimestamp = true;
    protected $createTime = 'create_at';
    protected $updateTime = 'update_at';


    const COLLECT_STATUS_YES = 1; //收藏
    const COLLECT_STATUS_NO = 2; //取消收藏

    //关联商品
    public function Goods()
    {
        return $this->hasOne('Goods', 'goods_id', 'goods_id');
    }
}