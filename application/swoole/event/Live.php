<?php
// +----------------------------------------------------------------------
// | 直播间操作类
// +----------------------------------------------------------------------
// | Copyright (c) 2019-2020 rights reserved.
// +----------------------------------------------------------------------
// | Author: wuyh
// +----------------------------------------------------------------------
// | Date: 2020/3/31 10:11
// +----------------------------------------------------------------------
namespace app\swoole\event;

use app\swoole\event\Base;
use app\common\service\LiveService;
use app\swoole\validate\Live AS LiveValidate;
use app\common\model\Live AS LiveModel;
use library\CacheKey;
use think\Log;


class Live extends Base
{
    /**
     * 开始直播
     * @Author: wuyh
     * @Date: 2020/3/18 19:20
     */
    public function startLive($data, \Live\Socket $socket)
    {
        if (empty($data)) return ['code' => 0, 'msg' => '参数错误'];
        $anchorInfo = $this->getAnchorInfo($data);
        if ($anchorInfo['code'] == 0) return ['code' => 0, 'msg' => $anchorInfo['msg']];

        $anchorInfo = $anchorInfo['data'];
        $validate = new LiveValidate();
        $data['anchor_id'] = $anchorInfo['id'];
        $validateRes = $validate->scene('start')->check($data);
        if ($validateRes == false) return ['code' => 0, 'msg' => $validate->getError()];

        $anchorInfo['fd'] = $data['fd'];
        $liveService = new LiveService();
        $params = [
            'live_id' => $data['live_id'],
            'fd' => $data['fd'],
            'anchor_info' => $anchorInfo,
        ];

        $res = $liveService->startLive($params);
        if ($res['code'] == 0) Log::record('开播失败: params : ' . var_export($params, true), ' 结果:' . var_export($res, true));
        return $res;
    }

    /**
     * 进入直播间
     * @param string $data
     * @return array
     * @Author: wuyh
     * @Date: 2020/3/18 20:18
     */
    public function intoLive($data, \Live\Socket $socket)
    {
        $res = $this->checkParams('user_role,fd,live_id', $data);
        if ($res['code'] == 0) return $res;

        $table = CacheKey::KET_LIVE_ON_START;
        $liveInfo = $this->redis->handler()->hGet($table, $data['live_id']);

        if (empty($liveInfo)) return ['code' => 0, 'msg' => '直播间不在开播状态'];
        $liveInfo = json_decode($liveInfo, true);

        if (!$liveInfo || $liveInfo['status'] != LiveModel::LIVE_BEGIN) return ['code' => 0, 'msg' => '直播间不在开播状态。'];
        if ($data['user_role'] == LiveModel::LIVE_USER_VISITOR){
            $userRole = LiveModel::LIVE_USER_MANAGE;

            //游客信息
            $info = [
                "user_id" => $data['fd'],
                "live_id" => $data['live_id'],
                "user_role" => $userRole,
                "nickname" => '游客'.$data['fd'],
                "enter_time" => time(),
                "fd" => $data['fd'],
            ];
        }else{
            if ($data['user_role'] == LiveModel::LIVE_USER_MANAGE) {
                $userRole = LiveModel::LIVE_USER_MANAGE;
                $userInfo = $this->getAnchorInfo(['token' => $data['token']]);
            } else {
                $userRole = LiveModel::LIVE_USER_NORMAL;
                $userInfo = $this->getUserInfo(['token' => $data['token']]);
            }

            if ($userInfo['code'] == 0) return ['code' => 0, 'msg' => '没有找到用户信息'];
            $info = [
                "user_id" => $userInfo['data']['id'],
                "live_id" => $data['live_id'],
                "user_role" => $userRole,
                "nickname" => $userInfo['data']['nickname'],
                "enter_time" => time(),
                "fd" => $data['fd'],
            ];
        }

        $table = CacheKey::get(CacheKey::KEY_LIVE_ROOM, ['live_id' => $info['live_id']]);
        $this->redis->handler()->hSet($table, $info['user_id'] . '_' . $userRole, json_encode($info));

        $table = CacheKey::get(CacheKey::KEY_LIVE_ROOM_ONLINE_USER, ['live_id' => $info['live_id']]);
        $this->redis->handler()->hSet($table, $info['user_id'] . '_' . $userRole, json_encode($info));

        return ['code' => 1, 'msg' => '进入成功', 'data' => ['live_info' => $liveInfo, 'user_info' => $info]];
    }

    /**
     * 结束直播
     * @param $data
     * @param \Live\Socket $socket
     * @return array
     * @Author: wuyh
     * @Date: 2020/4/1 19:14
     */
    public function stopLive($data, \Live\Socket $socket)
    {
        $res = $this->checkParams('token,fd,live_id', $data);
        if ($res['code'] == 0) return $res;

        $anchorInfo = $this->getAnchorInfo($data);
        if ($anchorInfo['code'] == 0) return ['code' => 0, 'msg' => $anchorInfo['msg']];
        $anchorInfo = $anchorInfo['data'];

        $params = [
            'live_id' => $data['live_id'],
            'fd' => $data['fd'],
            'anchor_info' => $anchorInfo,
        ];

        $liveService = new LiveService();
        $ret = $liveService->stopLive($params);

        if ($ret['code'] == 0) ['code' => 0, 'msg' => '关闭失败', 'data' => []];
        return ['code' => 1, 'msg' => '操作成功', 'data' => []];
    }

    /**
     * 下单支付成功通知直播间
     * @param $data
     * @param \Live\Socket $socket
     * @return array
     * @Author: wuyh
     * @Date: 2020/4/2 11:14
     */
    public function payOrder($data, \Live\Socket $socket)
    {
        $res = $this->checkParams('sign,live_id,user_id,order_amount,goods_name', $data);
        if ($res['code'] == 0) return $res;

        //判断订单否存在
        $data = [
            'user_id' => $data['user_id'],
            'order_amount' => $data['order_amount'],
            'live_id' => $data['live_id'],
            'goods_name' => $data['goods_name'],
            'goods_num' => $data['goods_num'],
            'live_order_stat' => []
        ];

        //实时直播间订单数据
        $orderStat = $this->redis->handler()->hGet(CacheKey::KET_LIVE_ROOM_ORDER_STAT, $data['live_id']);

        if ($orderStat) $data['live_order_stat'] = json_decode($orderStat, true);

        return ['code' => 1, 'msg' => '通知成功', 'data' => $data];
    }
}
