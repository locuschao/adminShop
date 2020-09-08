<?php
// +----------------------------------------------------------------------
// | 直播间互动相关操作
// +----------------------------------------------------------------------
// | Copyright (c) 2019-2020 rights reserved.
// +----------------------------------------------------------------------
// | Author: wuyh
// +----------------------------------------------------------------------
// | Date: 2020/3/21 14:01
// +----------------------------------------------------------------------
namespace app\swoole\controller;

use library\CacheKey;
use app\swoole\validate\Live AS LiveValidate;
use app\common\model\Live AS LiveModel;

class Live extends Base
{
    public function payOrder()
    {
        //通知主播（直播间实时数据）

        //通知房间所有人
        return false;
    }

    /**
     * 开始直播
     * @Author: wuyh
     * @Date: 2020/3/18 19:20
     */
    public function startLive($data, \Live\Socket $socket)
    {
        if (empty($data)) return json_encode(['code' => 0, 'msg' => 'error']);
        $validate = new LiveValidate();
        $validateRes = $validate->scene('start')->check($data);

        if ($validateRes == false) return json_encode(['code' => 0, 'msg' => $validate->getError()]);

        $live = LiveModel::where(['id' => $data['live_id'], 'anchor_id' => $data['user_id'], 'status' => LiveModel::LIVE_BEGIN])->find();

        if (!$live)  return false;

        //这里不做开播的处理,只是通知正在直播间的用户可以拉流



        return true;
    }
}