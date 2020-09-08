<?php
// +----------------------------------------------------------------------
// | 腾讯云IM回调事件监听
// +----------------------------------------------------------------------
// | Author: Wuyh 2020-02-26
// +----------------------------------------------------------------------
namespace app\api\behavior;

use think\controller;
use app\common\model\Live;

class ImCallback extends controller
{
    public function _initialize()
    {

    }

    /**
     * 执行
     * @param $params
     * @throws \think\exception\DbException
     */
    public function run(&$params)
    {
        if ($params) {
            // CallbackCommand 回调命令 事件类型
            switch ($params['CallbackCommand']) {
                //群组解散之后回调
                case 'Group.CallbackAfterGroupDestroyed':
                    $params['ok'] = self::groupDestroyed($params);
                    break;
                case '':
                default:
                    break;
            }
        }
    }

    /**
     * 结束直播，群解散
     * @param $params
     * @return bool
     */
    private function groupDestroyed(&$params)
    {
        $liveId = intval($params['GroupId']);
        if (empty($liveId)) return false;

        //直播间停播
        return Live::stopLive($liveId);
    }
}
