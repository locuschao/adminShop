<?php
// +----------------------------------------------------------------------
// | 缓存的所有KEY值
// +----------------------------------------------------------------------
// | Author: Wuyh 2020-02-26
// +----------------------------------------------------------------------
namespace library;

class CacheKey
{
    const SITE_SYS_CONFIG = "site_sys_config";  //平台系统配置

    //直播房间 - hash
    const KEY_LIVE_ROOM = 'Live:live_room_{live_id}';

    // 直播间主播表 - hash
    const KEY_LIVE_ROOM_ANCHOR = 'Live:live_room_anchor_list';

    //直播客户端场景 - hash
    const KEY_FD_SCENE = 'Live:live_fd_scene';

    //直播间游客的UserSig表 - hash
    const KEY_IM_SIG = 'Live:im_list';

    //正在开播的直播详情缓存表 - hash
    const KET_LIVE_ON_START = 'Live:live_on_start';

    //实时在房间的用户 - hash
    const KEY_LIVE_ROOM_ONLINE_USER = 'Live:live_online_user_list_{live_id}';

    //直播间实时的订单数据统计
    const KET_LIVE_ROOM_ORDER_STAT = 'Live:live_order_stat';

    /**
     * 获取指定redis key值
     * @param $format
     * @param $param
     * @return mixed
     * @Author: wuyh
     * @Date: 2020/3/19 16:03
     */
    public static function get($format, $param) {
        foreach ($param as $key => $val) {
            $dst = '{' . $key . '}';
            $format = str_replace($dst, $val, $format);
        }
        return $format;
    }
}