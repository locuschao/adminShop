<?php
/**
 * [用户领取直播间持久化]
 */
namespace app\common\model;
use think\Exception;

class LiveItemUser extends Base{
    protected $name = 'live_item_user';

    const live_sign = "live_sign";//直播间签到
    const live_gift = "live_gift";//直播间赠礼
    const live_share = "live_share";//直播间分享
    const live_buy  = "live_buy";//直播间购物
    const live_view = "live_view";//直播间观看
    const live_draw = "live_draw";//直播间抽奖
    const live_answer = "live_answer";//直播间问答

    //添加
    public function addLiveItemUser($mode_type, $params) {
        if (empty($mode_type) || empty($params)) {
            return false;
        }
        try {
            $data = array(
                "user_id"=> $params['user_id'],
                "live_id"=> $params['live_id'],
                "mode_type" => $mode_type,
                "content" => json_encode($params),
                "insertymd" => date("Ymd"),
                "insert_time" => time(),
            );

            $this->insertGetId($data);
        }catch (Exception $e) {
        }

        return true;
    }

    //查询用户领取列表
    public function getUserLiveItemByCondition(array $condition){
        $list = $this->where($condition)->select();
        return empty($list)?array():$list->toArray();
    }

}