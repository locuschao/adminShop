<?php
// +----------------------------------------------------------------------
// | 直播间SOCKET互动
// +----------------------------------------------------------------------
// | Copyright (c) 2019-2020 rights reserved.
// +----------------------------------------------------------------------
// | Author: wuyh
// +----------------------------------------------------------------------
// | Date: 2020/3/19 14:06
// +----------------------------------------------------------------------
namespace Live;

use app\common\model\Live;
use think\Cache;
use think\Config;
use library\CacheKey;

class Socket
{
    protected $redis;

    protected $fd;

    protected $server;

    protected $cmd;

    /**
     * 模块
     * @var string
     */
    protected $moudle = 'swoole';

    public function __construct()
    {
        $this->redis = Cache::connect(Config::get('cache.redis'));
    }

    /**
     * 接管服务消息监听
     * @param $server
     * @param $data
     * @param $fd
     * @return bool|mixed
     * @Author: wuyh
     * @Date: 2020/3/21 14:27
     */
    public function onMessage($server, $data, $fd)
    {
        $this->fd = $fd;
        $this->server = $server;
        $data = json_decode($data, true);

        if (empty($data)) {
            return $this->_error('参数有误');
        }

        if (empty($data['cmd']) || empty($data['module'])) return $this->_error('cmd 或者 module参数有误');
        if (empty($data['content']) || !is_array($data['content'])) return $this->_error('content 参数有误');

        $controller = controller($this->moudle . '/' . ucfirst($data['module']), 'event');
        $data['content']['fd'] = $fd;
        $this->cmd = $data['cmd'];

        if (empty($controller)) return $this->_error('没有找到对应模块');
        $cmd = $this->_getCmd($data['cmd']);

        if (!$cmd) return $this->_error('没有找到对应方法');
        $ret = $this->handler($controller, $cmd, $data['content']);

        if ($ret['code'] == 0) return $this->_error($ret['msg']);

        if ($data['cmd'] == 'into_live') {
            //发送给直播间所有人
            //$server->task($ret);
            $this->sendAll($server, [
                'type' => 'user_enter',
                'from_user' => $ret['data']['user_info']['user_id'],
                'to_user' => 'all',
                'nickname' => $ret['data']['user_info']['nickname'],
                'user_role' => $ret['data']['user_info']['user_role'],
                'live_id' => $ret['data']['user_info']['live_id']
            ],
                $fd);

            //返回直播间的一些信息给用户
            $this->send($server, $fd, $ret['data']['live_info']);

        } else {
            //各种骚操作
            switch ($data['cmd']) {
                case 'start_live' :
                    $liveOrderInfo = [
                        'order_num' => 0,
                        'order_amount' => 0,
                    ];
                    return $this->_success('开播成功', $liveOrderInfo);

                    //开始直播,消息通知
                    break;
                case 'stop_live':
                    return $this->_success('关闭成功');
                    break;
            }
        }

        return ['code' => 1, 'msg' => 'success'];
    }

    /**
     * 接受HTTP请求，推送执行异步任务
     * @param $req
     * @param $resp
     * @param $data
     * @param $server
     * @return array
     * @Author: wuyh
     * @Date: 2020/4/2 11:27
     */
    public function onRequest($req, $resp, $data, $server)
    {
        $this->server = $server;
        if (empty($data['cmd']) || empty($data['module'])) return $this->_error('cmd 或者 module参数有误');
        if (empty($data['content']) || !is_array($data['content'])) return $this->_error('content 参数有误');

        $controller = controller($this->moudle . '/' . ucfirst($data['module']), 'event');
        if (empty($controller)) return $this->_error('没有找到对应模块');

        $cmd = $this->_getCmd($data['cmd']);
        if (!$cmd) return $this->_error('没有找到对应方法');
        $ret = $this->handler($controller, $cmd, $data['content']);

        if ($ret['code'] == 0) return $this->_error($ret['msg']);

        switch ($data['cmd']) {
            case 'pay_order':
                $uid = $this->_getLiveUserId($ret['data']['user_id']);
                $userInfo = $this->getUser($ret['data']['live_id'], $uid, 0, false);
                if (empty($userInfo)) return ['code' => 0, 'msg' => '未找到用户信息'];

                $orderInfo = [
                    'type' => 'user_pay_order',
                    'live_id' => $ret['data']['live_id'],
                    'from_user' => $ret['data']['user_id'],
                    'to_user' => 'all',
                    'nickname' => $userInfo['nickname'],
                    'user_role' => $userInfo['user_role'],
//                    'head_url' => $userInfo['head_url'],
                    'order_amount' => $ret['data']['order_amount'],
                ];

                $this->sendAll($server, $orderInfo);

                //通知主播
                $anchor = $this->redis->handler()->hget(CacheKey::KEY_LIVE_ROOM_ANCHOR, $ret['data']['live_id']);
                if ($anchor) {
                    $anchor = json_decode($anchor, true);
                    $liveOrderStat = $ret['data']['live_order_stat'];
                    $liveOrderStat['type'] = 'order_stat';

                    $this->send($server, $anchor['fd'], $liveOrderStat);
                }

                break;
        }

        $ret = ['code' => 1, 'msg' => '成功', 'data' => []];
        return $ret;
    }


    /**
     * 获取直播房间缓存表
     * @param $table
     * @param $liveId
     * @return string
     * @Author: wuyh
     * @Date: 2020/3/19 15:14
     */
    public function getTable($table, $liveId)
    {
        return CacheKey::get($table, ['live_id' => $liveId]);
    }

    /**
     * 黑名单或禁止的用户
     * @param int $liveId
     * @param int $uid
     * @return array
     * @Author: wuyh
     * @Date: 2020/3/18 15:25
     */
    public function getBanned($liveId = 0, $uid = 0)
    {
        $return = array();
        $table = $this->getTable('banned', $liveId);
        $return['all'] = $this->redis->handler()->hGet($table, 'bannedAll');
        if (empty($uid)) {
            $return['self'] = 1;
        } else {
            $return['self'] = $this->redis->handler()->hGet($table, $uid);
        }

        return $return;
    }

    /**
     * 设置客户端所在场景
     * @param $fd
     * @param $scene
     * @return bool
     * @Author: wuyh
     * @Date: 2020/3/18 10:02
     */
    public function setScene($fd, $scene)
    {
        $scene = (is_array($scene) ? json_encode($scene) : $scene);
        $this->redis->handler()->hSet(CacheKey::KEY_FD_SCENE, $fd, $scene);
        return true;
    }

    /**
     * 获取客户端所在场景
     * @param $fd
     * @return bool|mixed
     * @Author: wuyh
     * @Date: 2020/3/28 11:16
     */
    public function getScene($fd)
    {
        $scene = $this->redis->handler()->hGet(CacheKey::KEY_FD_SCENE, $fd);
        $scene = (!empty($scene) ? json_decode($scene, true) : false);
        return $scene;
    }

    /**删除客户端所在场景
     * @param $fd
     * @return bool
     * @Author: wuyh
     * @Date: 2020/3/18 10:56
     */
    public function delScene($fd)
    {
        $this->redis->handler()->hDel(CacheKey::KEY_FD_SCENE, $fd);
        return true;
    }

    /**
     * 发送消息
     * @param null $server
     * @param int $fd
     * @param array $data
     * @param int $code
     * @param string $msg
     * @return bool
     * @Author: wuyh
     * @Date: 2020/4/3 14:44
     */
    public function send($server = null, $fd = 0, $data = [], $code = 1, $msg = 'Success')
    {
        if (empty($server) || empty($fd) || empty($data)) return false;

        if (!($server->exist($fd))) {
            $this->delUser($server, $data['live_id'], $fd, true, false);
            return true;
        }

//        if (is_array($data)) {
//            unset($data['live_id']);
            if (!(isset($data['time']))) $data['time'] = time();
//            if (!empty($this->cmd)) $data['cmd'] = $this->cmd;
//            $data = json_encode($data, JSON_UNESCAPED_UNICODE);
//        }

        $ret =[
            'code' => $code,
            'msg' => $msg,
            'data' => $data,
            'cmd' => empty($this->cmd) ? 'sys_msg' : $this->cmd,
        ];

        $ret = json_encode($ret, JSON_UNESCAPED_UNICODE);

        $server->push($fd, $ret);
        return true;
    }

    /**
     * 发送给所有人
     * @param $server
     * @return bool
     * @Author: wuyh
     * @Date: 2020/3/19 15:14
     */
    public function sendAll($server, $data, $fd = 0, $code = 1, $msg = 'Success')
    {
        if (empty($server) || empty($data) || empty($data['live_id'])) return false;

        $allUser = $this->getAllUser($data['live_id']);

        if (empty($allUser)) return true;

        //备注wuyh，这里要改成异步推送消息
        foreach ($allUser as $k => $user) {
            if (empty($user)) continue;

            $user = json_decode($user, true);
            if ($k == $data['from_user']) $data['self'] = 1; //发消息给自己
            $this->send($server, $user['fd'], $data, $code, $msg);
        }
        return true;
    }

    /**
     * 删除用户
     * @param $server
     * @param int $liveId
     * @param int $uid
     * @param bool $isFd
     * @param bool $notice
     * @return bool
     * @Author: wuyh
     * @Date: 2020/3/20 10:45
     */
    public function delUser($server, $liveId = 0, $uid = 0, $isFd = false, $notice = true)
    {
        if (empty($liveId) || empty($uid)) return false;

        $uid = $this->_getLiveUserId($uid);
        $table = $this->getTable(CacheKey::KEY_LIVE_ROOM_ONLINE_USER, $liveId);
        if ($isFd) {
            $user = $this->getUser($liveId, $uid, true);
        } else {
            $user = $this->redis->handler()->hGet($table, $uid);
            $user = ((!(empty($user)) ? json_decode($user, true) : false));
        }

        if (!(empty($user))) {
            if ($notice) {
                //通知
//                $server
            }
            $this->redis->handler()->hDel($table, $user['id']);
        }
    }

    /**
     * 获取用户
     * @param int $liveId
     * @param int $userId
     * @param bool $isFd
     * @param bool $onLive //此刻是否在直播间
     * @return bool|mixed
     * @Author: wuyh
     * @Date: 2020/4/2 11:46
     */
    public function getUser($liveId = 0, $userId = 0, $isFd = false, $onLive = true)
    {
        if (empty($liveId) && empty($userId)) return false;

        $key = $onLive ? CacheKey::KEY_LIVE_ROOM_ONLINE_USER : CacheKey::KEY_LIVE_ROOM;
        $table = $this->getTable($key, $liveId);

        if (!$isFd) {
            $user = $this->redis->handler()->hGet($table, $userId);
        } else {
            $user = false;
            $allUser = $this->getAllUser($liveId, $table);

            if (!(empty($allUser))) {
                foreach ($allUser as $key => $value) {
                    if (empty($value)) continue;

                    $value = json_decode($value, true);

                    if ($value['fd'] == $userId) {
                        $user = $value;
                        break;
                    }
                }
            }
        }

        if (!(empty($user)) && !(is_array($user))) $user = json_decode($user, true);

        return $user;
    }

    /**
     * 获取直播间所有用户
     * @param int $liveId
     * @return mixed
     * @param bool $onLive //此刻是否在直播间
     * @Author: wuyh
     * @Date: 2020/3/21 16:43
     */
    public function getAllUser($liveId = 0, $onLive = true)
    {
        $key = $onLive ? CacheKey::KEY_LIVE_ROOM_ONLINE_USER : CacheKey::KEY_LIVE_ROOM;
        $table = $this->getTable($key, $liveId);
        $list = $this->redis->handler()->hGetAll($table);
        return $list;
    }

    /**
     * 执行
     * @param $controller
     * @param $cmd
     * @param $data
     * @return mixed
     * @Author: wuyh
     * @Date: 2020/3/31 17:39
     */
    public function handler($controller, $cmd, $data)
    {
        $ret = call_user_func_array([$controller, $cmd], [$data, $this]);
        return $ret;
    }

    /**
     * 服务端失败响应
     * @param string $msg
     * @param array $data
     * @return array
     * @Author: wuyh
     * @Date: 2020/4/1 19:05
     */
    public function _error($msg = 'Error', $data = [])
    {
        $ret = ['code' => 0, 'msg' => $msg, 'data' => $data];
        if ($this->fd) $this->send($this->server, $this->fd, $data, 0, $msg);
        return $ret;
    }

    /**
     * @param string $msg
     * @param $data
     * @return array
     * @Author: wuyh
     * @Date: 2020/4/1 19:55
     */
    public function _success($msg = 'Success', $data = [])
    {
        $ret = ['code' => 1, 'msg' => $msg, 'data' => $data];
        if ($this->fd) $this->send($this->server, $this->fd, $data, 1, $msg);
        return $ret;
    }

    /**
     * 执行的方法
     * @param $cmd
     * @return string|string[]|null
     * @Author: wuyh
     * @Date: 2020/4/2 11:54
     */
    private function _getCmd($cmd)
    {
        $str = preg_replace_callback('/([-_]+([a-z]{1}))/i', function ($matches) {
            return strtoupper($matches[2]);
        }, $cmd);
        return $str;
    }

    /**
     * 获取用户在缓存中的ID标识
     * @param $uid
     * @param $role
     * @return string
     * @Author: wuyh
     * @Date: 2020/4/2 12:11
     */
    private function _getLiveUserId($uid, $role = Live::LIVE_USER_NORMAL)
    {
        return $uid . '_' . $role;
    }
}





