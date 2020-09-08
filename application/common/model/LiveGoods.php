<?php
// +----------------------------------------------------------------------
// | 直播商品
// +----------------------------------------------------------------------
// | Author: Wuyh 2020-02-07
// +----------------------------------------------------------------------
namespace app\common\model;

use think\Model;

class LiveGoods extends Model
{
    protected $name = 'live_goods';
    protected $resultSetType = 'array';
    protected $autoWriteTimestamp = true;
    protected $createTime = 'create_at';
    protected $updateTime = 'update_at';

    /**
     * 状态
     */
    const LIVE_WAIT = 0;
    const LIVE_BEGIN = 1;
    const LIVE_END = 2;

    /**
     * 保存直播商品
     * @param $liveId
     * @param $goodIds
     * @return bool
     * @throws \Exception
     * @author wuyh
     */
    public function saveGoods($liveId, $goodIds)
    {
        $this->where(['live_id' => $liveId])->delete();

        $goodIds = explode(',', $goodIds);

        $goods = $this->goods()
            ->field('goods_id,goods_sn,shop_price')
            ->where(['goods_id' => ['in', $goodIds]])
            ->select()
            ->toArray();

        foreach ($goods as &$good){
            $good['live_id'] = $liveId;
            $good['price'] = $good['shop_price'];
        }

        $this->allowField(true)->saveAll($goods);

        return true;
    }

    /**
     * 关联商品
     * @return \think\model\relation\HasOne
     */
    public function goods()
    {
        return $this->hasOne('Goods', 'goods_id', 'goods_id');
    }

    //获取直播商品列表
    public function fetchList(array $where){
        $list = $this->where($where)->select();
        return empty($list)?array():$list->toArray();
    }

    //获取直播单个商品详情
    public function getDetail($where){
        $list = $this->alias('a')->join('cc_live b','a.live_id=b.id','left')->where($where)->find();
        return empty($list)?array():$list->toArray();
    }
}