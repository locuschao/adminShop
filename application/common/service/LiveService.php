<?php
//直播商品类
namespace app\common\service;

use app\common\model\Live;
use app\common\model\LiveGoods;
use app\common\model\LiveUser;
use library\CacheKey;
use think\Cache;
use think\Hook;

class LiveService extends BaseService
{
    protected $prefix = "live";

    //获取所有申请的商品
    public function getLiveGoodsListByGoodsIdsCache($goods_ids)
    {
        $goods_ids = array_filter($goods_ids, 'intval');
        if (empty($goods_ids)) {
            return array();
        }
        $where = array();
        $where['goods_id'] = array('in', $goods_ids);
        $where['status'] = 1;
        $cache = md5($this->prefix . 'getLiveGoodsListByGoodsIdsCache' . serialize($where));
        if (Cache::store('redis')->has($cache)) {
            return Cache::store('redis')->get($cache);
        }
        $liveGoodsModel = new LiveGoods();
        $list = $liveGoodsModel->fetchList($where);
        Cache::store('redis')->set($cache, $list, $this->time);
        return $list;
    }

    //获取直播商品详情
    public function getLiveGoodsInfoByGoodsIdCache($goods_id)
    {
        $goods_id = intval($goods_id);
        if (empty($goods_id)) {
            return array();
        }
        $where = array();
        $where['a.goods_id'] = $goods_id;
        $where['a.status'] = 1;
        $where['b.status'] = 1;
        $cache = md5($this->prefix . 'getLiveGoodsInfoByGoodsIdCache' . serialize($where));

        if (Cache::store('redis')->has($cache)) {
            return Cache::store('redis')->get($cache);
        }
        $liveGoodsModel = new LiveGoods();

        $list = $liveGoodsModel->getDetail($where);

        Cache::store('redis')->set($cache, $list, $this->time);
        return $list;
    }


    /**
     * 直播详情
     * @param $liveId
     * @return array|false|\PDOStatement|string|\think\Model
     * @Author: wuyh
     * @Date: 2020/3/31 13:52
     */
    public function getLiveDetail($liveId)
    {
        $live = new Live();
        $info = $live->with(['liveItemAnswer', 'liveItemDraw', 'liveItemView', 'liveGoods','liveAnchor'])->where(['id' => $liveId])->find();

        if (empty($info)) return [];
        return $info->toArray();
    }

    /**
     * 开始直播
     * @param $params
     * @return array
     * @Author: wuyh
     * @Date: 2020/3/31 13:52
     */
    public function startLive($params)
    {
        $info = $this->getLiveDetail($params['live_id']);
        if (empty($info)) return ['code' => 0, 'msg' => '没有找到直播信息'];
        $time = time();
        $liveModel = new Live();
        $anchorId = $params['anchor_info']['id'];

        if ($info['status'] != Live::LIVE_WAIT && $anchorId != $info['anchor_id']) return ['code' => 0, 'msg' => '主播与开播的主播ID不同'];
        $res = $liveModel->allowField(true)->save([
            'status' => Live::LIVE_BEGIN,
            'anchor_id' => $anchorId,
            'begin_time' => $time,
        ],
            [
                'id' => $params['live_id']
            ]);

        if ($res === false) return ['code' => 0, 'msg' => '开播失败 - 更改状态失败。' . $liveModel->getError()];

        $table = CacheKey::get(CacheKey::KET_LIVE_ON_START, ['live_id' => $params['live_id']]);
        $this->redisCache->hSet($table, $params['live_id'], json_encode($info, JSON_UNESCAPED_UNICODE));

        $table = CacheKey::get(CacheKey::KEY_LIVE_ROOM_ANCHOR, ['live_id' => $params['live_id']]);
        $this->redisCache->hSet($table, $params['live_id'], json_encode($params['anchor_info'], JSON_UNESCAPED_UNICODE));

        $info = [
            "user_id" => $params['anchor_info']['id'],
            "live_id" => $params['live_id'],
            "user_role" => Live::LIVE_USER_MANAGE,
            "nickname" => $params['anchor_info']['nickname'],
            "enter_time" => time(),
            "fd" => $params["fd"],
        ];

        $table = CacheKey::get(CacheKey::KEY_LIVE_ROOM, ['live_id' => $info['live_id']]);
        $this->redisCache->hSet($table, $info['user_id'] . '_' . $info['user_role'], json_encode($info));

        // 直播间信息统计
        $orderStat = $this->redisCache->hGet(CacheKey::KET_LIVE_ROOM_ORDER_STAT, $params['live_id']);
        if (empty($orderStat)) {
            $orderStat = [
                'order_num' => 0, //订单数
                'order_amount' => 0, //订单金额
                'watch_num' => 0, //累计观看人数（这个额外统计）
            ];

            $this->redisCache->hSet(CacheKey::KET_LIVE_ROOM_ORDER_STAT, $params['live_id'], json_encode($orderStat));
        }

        return ['code' => 1, 'msg' => '开播成功'];
    }

    /**
     *
     * @param $params
     * @return array
     * @Author: wuyh
     * @Date: 2020/4/1 19:21
     */
    public function stopLive($params)
    {
        $info = $this->getLiveDetail($params['live_id']);
        if (empty($info)) return ['code' => 0, 'msg' => '没有找到直播信息'];

        $time = time();
        $liveModel = new Live();
        $anchorId = $params['anchor_info']['id'];
        if ($info['status'] == Live::LIVE_END) return ['code' => 1, 'msg' => '关闭成功'];
        if ($anchorId != $info['anchor_id']) return ['code' => 0, 'msg' => '主播与开播的主播ID不同'];

        $res = $liveModel->allowField(true)->save([
            'status' => Live::LIVE_END,
            'end_time' => $time,
        ],
            [
                'id' => $params['live_id'],
                'anchor_id' => $anchorId,
            ]);

        if ($res === false) return ['code' => 0, 'msg' => '关闭直播失败 - 更改状态失败。' . $liveModel->getError()];

        $table = CacheKey::get(CacheKey::KEY_LIVE_ROOM_ONLINE_USER, ['live_id' => $info['id']]);
        $this->redisCache->Hdel($table);
        $this->redisCache->Hdel(CacheKey::KEY_LIVE_ROOM_ANCHOR, $info['id']);

        return ['code' => 1, 'msg' => '关闭成功'];
    }

}