<?php
// +----------------------------------------------------------------------
// | 直播订阅
// +----------------------------------------------------------------------
// | Author: Wuyh 2020-03-20
// +----------------------------------------------------------------------

namespace app\common\model;

use think\Model;

class LiveSubscribe extends Model
{
    protected $name = 'live_subscribe';
    protected $resultSetType = 'collection';
    protected $autoWriteTimestamp = true;
    protected $createTime = 'create_at';
    protected $updateTime = 'update_at';

    const SUBSCRIBE_STATUS_YES = 1; //订阅
    const SUBSCRIBE_STATUS_NO = 2; //取消订阅

    /**
     * 接口使用 - 获取不同直播
     * @param $params
     * @return array
     * @Author: wuyh
     * @Date: 2020/3/23 13:11
     */
    public function getListFromApi($params)
    {
        switch ($params['status']) {
            case Live::LIVE_BEGIN:
            case Live::LIVE_WAIT:
                $order = 'v.start_time ASC';
                break;
            case Live::LIVE_END:
                $order = 'v.end_time DESC';
                break;
            default:
                $order = 'v.start_time DESC';
                break;
        }

        $page = $params['page'] ? $params['page'] : 1;
        $limit = $params['limit'] ? $params['limit'] : config('cfg.ORDER_PAGE_LIMIT');
        $status = intval($params['status']);
        $map = [
            'l.user_id' => $params['user_id'],
            'v.status' => $status
        ];

        $count = $this->alias('l')
            ->join('__LIVE__ v', 'l.live_id = v.id', 'LEFT')
            ->where($map)
            ->count();

        $list = $this->alias('l')
            ->field('l.*, v.title,v.start_time,v.end_time,v.img_url,v.memo,v.anchor_id')
            ->join('__LIVE__ v', 'l.live_id = v.id', 'LEFT')
            ->where($map)
            ->order($order)
            ->page($page, $limit)
            ->select();

        $list =  (!empty($list)) ? $list->toArray() : [];
        return ['data' => $list, 'count' => $count];
    }

    /**
     * 关联直播
     * @return \think\model\relation\HasOne
     * @author wuyh
     */
    public function Live()
    {
        return $this->hasOne('Live', 'id', 'live_id')->field('start_time,title,img_url,status');
    }
}