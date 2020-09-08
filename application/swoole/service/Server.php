<?php
// +----------------------------------------------------------------------
// | WEBSOCKET 服务
// +----------------------------------------------------------------------
// | Copyright (c) 2019-2020 rights reserved.
// +----------------------------------------------------------------------
// | Author: wuyh
// +----------------------------------------------------------------------
// | Date: 2020/3/17 17:24
// +----------------------------------------------------------------------

namespace app\swoole\service;

use think\Log;
use Live\Socket;

class Server
{
    /**
     * 建立连接
     * @param $server
     * @param $request
     * @Author: wuyh
     * @Date: 2020/3/18 17:56
     */
    public function onOpen($server, $request)
    {
        Log::record("server: 连接成功 success, 客户端fd{$request->fd}", 'debug');
        Log::record($request, 'debug');
        $server->push($request->fd, json_encode(['connected', 'connetcting......']));

        //这里不做进入房间的逻辑处理， 进入房间必须向服务器发送into_live信息
    }

    /**
     * 通讯
     * @param $server
     * @param $frame
     * @return bool
     * @Author: wuyh
     * @Date: 2020/3/18 20:20
     */
    public function onMessage($server, $frame)
    {
        Log::record("接收信息 from {$frame->fd}:{$frame->data},opcode:{$frame->opcode},fin:{$frame->finish}", 'debug');

        $data = $this->parserParam($frame->data);
        if (empty($data)) return false;

        $params = json_decode($data, true);

        $socket = new Socket();
        $ret = $socket->onMessage($server, $data, $frame->fd);
        if ($ret['code'] == 1) $socket->setScene($frame->fd, $params);
    }

    /**
     * 断开连接
     * @param $server
     * @param $fd
     * @return bool
     * @Author: wuyh
     * @Date: 2020/3/19 20:06
     */
    public function onClose($server, $fd)
    {
        Log::record('WebSocket->onClose', 'debug');
        Log::record("client {$fd} closed", 'debug');

        $socket = new Socket();
        $data = $socket->getScene($fd);
        if (!$data) echo 'scene丢失，fd:' . $fd . PHP_EOL;

        $socket->delScene($fd);

        if (empty($data['live_id'])) return false;

        if (!empty($data['scene'])) {
            if (empty($data['live_id'])) return false;

            $isFd = true;
            if (!(empty($data['user_id']))) {
                $fd = $data['user_id'];
                $isFd = false;
            }

            //删除房间的缓存
            $socket->delUser($server, $data['live_id'], $fd, $isFd);
        }
        return true;
    }

    /**
     * 任务
     * @param $serv
     * @param $task_id
     * @param $from_id
     * @param $data
     * @Author: wuyh
     * @Date: 2020/3/18 17:57
     */
    public function onTask($serv, $task_id, $from_id, $data)
    {
//        $result = json_decode($data, true);
        $serv->finish(0);
    }

    /**
     * 任务完成
     * @param $serv
     * @param $task_id
     * @param $data
     * @return bool
     * @Author: wuyh
     * @Date: 2020/3/19 18:29
     */
    public function onFinish($serv, $task_id, $data)
    {
        return true;
    }

    /**
     * HTTP请求
     * @param $request
     * @param $response
     * @return mixed
     * @Author: wuyh
     * @Date: 2020/3/18 14:33
     */
    public function onRequest($request, $response, $server)
    {
        $params = $request->rawContent();
        $params = json_decode($params, true);
        if (empty($params)) $response->end(json_encode(['code' => 0, 'msg' => 'error']));

        //这里需要加签名验证
//        $vetify = true;
//        if (!$vetify)  $response->end(json_encode(['code' => 0, 'msg' => 'sign error']));
//
        $socket = new Socket();
//        $ret = $socket->checkSing($params);
        $ret = $socket->onRequest($request, $response, $params, $server);
        $response->end(json_encode($ret));
    }

    /**
     * 解析参数
     * @param $obj
     * @return mixed
     * @Author: wuyh
     * @Date: 2020/3/19 10:46
     */
    public function parserParam($obj)
    {
        if (!is_array($obj)) {
            $obj = stripslashes($obj);
            $obj = htmlspecialchars_decode($obj);
        } else {
            foreach ($obj as $k => &$v) {
                $v = stripslashes($v);
                $v = htmlspecialchars_decode($v);
            }
        }

        return $obj;
    }
}