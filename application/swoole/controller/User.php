<?php
// +----------------------------------------------------------------------
// | 直播间用户互动
// +----------------------------------------------------------------------
// | Copyright (c) 2019-2020 rights reserved.
// +----------------------------------------------------------------------
// | Author: wuyh
// +----------------------------------------------------------------------
// | Date: 2020/3/19 13:14
// +----------------------------------------------------------------------
namespace app\swoole\controller;

use library\CacheKey;
use app\swoole\validate\Live AS LiveValidate;
use app\common\model\Live;

class User extends Base
{


    /**
     * 建立连接
     * @return false|string
     * @Author: wuyh
     * @Date: 2020/3/18 15:04
     */
    public function successLink()
    {
        $params = $_GET;
        return json_encode($params);
    }

    /**
     * 进入直播间
     * @param string $data
     * @return false|string
     * @Author: wuyh
     * @Date: 2020/3/18 20:18
     */
    public function intoLive($data, \Live\Socket $socket)
    {
        if (empty($data)) return json_encode(['code' => 0, 'msg' => 'error']);
        $validate = new LiveValidate();
        $validateRes = $validate->scene('intoLive')->check($data);

        if ($validateRes == false) return json_encode(['code' => 0, 'msg' => $validate->getError()]);

        $info = [
            "user_id"  => $data['user_id'],
            "live_id" => $data['live_id'],
            "user_role" => $data['user_role'],
            "nickname" => $data['nickname'],
            "enter_time" => time(),
            "fd" => $data["fd"],
        ];

        $table = $socket->getTable(CacheKey::KEY_LIVE_ROOM, $info['live_id']);
        $this->redis->handler()->hSet($table, $info['user_id'], json_encode($info));

        if ($info['user_role'] == Live::LIVE_USER_MANAGE){
            $table = $socket->getTable(CacheKey::KEY_LIVE_ROOM_ANCHOR, $info['live_id']);
            $this->redis->handler()->hSet($table, $info['live_id'], json_encode(['anchor_id' => $data['user_id'], 'fd' => $info['fd']]));
        }

        return $info;
    }




    /**
     * 直播间实时统计信息
     * @param array $get
     * @param array $post
     * @param array $input
     * @return bool
     * @Author: wuyh
     * @Date: 2020/3/18 20:22
     */
    public function liveStatistics($get = [], $post = [], $input = [])
    {
        return true;
    }

    /**
     * 离开直播间
     * @param $server
     * @param int $liveId
     * @param int $uid
     * @param bool $isFd
     * @return bool
     * @Author: wuyh
     * @Date: 2020/3/18 20:39
     */
    protected function exitLive($server, $liveId = 0, $uid = 0, $isFd = false)
    {
        if (empty($roomid) || empty($uid)) return false;

        $table = $this->socket->getTable(CacheKey::KEY_LIVE_ROOM, $liveId);

        if ($isFd) {
            $user = $this->socket->getUser($roomid, $uid, true);
        } else {
            $user = redis()->hGet($table, $uid);
            $user = ((!(empty($user)) ? json_decode($user, true) : false));
        }

        if (!(empty($user))) $this->redis->handler()->hDel($table, $user['user_id']);

        return true;
    }
}