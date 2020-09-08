<?php
// +----------------------------------------------------------------------
// | 订阅直播接口
// +----------------------------------------------------------------------
// | Author: Wuyh 2020-03-21
// +----------------------------------------------------------------------
namespace app\api\controller\live\v1;

use app\api\controller\live\Base;
use app\common\model\LiveSubscribe AS LiveSubscribeModel;
use app\common\model\Live;
use library\Code;
use think\Exception;
use think\Db;

class LiveSubscribe extends Base
{
    /**
     * 订阅直播列表
     * @return \think\response
     * @Author: wuyh
     * @Date: 2020/3/20 13:14
     */
    public function getList()
    {
        if (empty($this->userInfo['id'])) return $this->_error('TOKEN_TIME_OUT');
        $data = [
            'status' => $this->_param('status'),
            'user_id' => $this->userInfo['id'],
            'page' => $this->_param('page'),
            'limit' => $this->_param('limit')
        ];

        $liveSubscribe = new LiveSubscribeModel();
        $data = $liveSubscribe->getListFromApi($data);

        $count = $data['count'];
        $list = $data['data'];

        $this->_response['data']['count'] = $count;
        $this->_response['data']['list'] = $list;

        $this->_success('v1.Live.LiveSubscribe:getList');
    }

    /**
     * 订阅直播
     * @return \think\response
     * @Author: wuyh
     * @Date: 2020/3/20 13:37
     */
    public function subscribe()
    {
        if (empty($this->userInfo)) return $this->_error('TOKEN_TIME_OUT');
        $liveId = $this->_param('live_id');
        $isNotify = $this->_param('is_notify');
        if (empty($liveId)) return $this->_error('PARAM_ERROR');
        $live = Live::where(["id" => $liveId])->find();

        Code::$code['HAS_LIVE_SUBSCRIBE'] = ['code' => '400', 'msg' => '您已经订阅过该直播'];
        Code::$code['LIVE_NO_FOUND_ERROR'] = ['code' => '400', 'msg' => '直播不存在'];
        Code::$code['LIVE_SUBSCRIBE_ERROR'] = ['code' => '400', 'msg' => '订阅失败'];
        Code::$code['LIVE_IS_END'] = ['code' => '400', 'msg' => '直播已经结束，不能订阅'];

        if (!$live) $this->_error('LIVE_NO_FOUND_ERROR');
        if ($live['status'] == LIVE::LIVE_END) $this->_error('LIVE_IS_END');

        $subscribe = $this->hasSubscribe($this->userInfo['id'], $liveId);
        if ($subscribe && $subscribe['status'] == LiveSubscribeModel::SUBSCRIBE_STATUS_YES) return $this->_error('HAS_LIVE_SUBSCRIBE');

        try {
            Db::startTrans();

            $liveSubscribeModel = new LiveSubscribeModel();

            if ($subscribe) {
                $res = $liveSubscribeModel->save([
                    'is_notify' => $isNotify,
                    'status' => LiveSubscribeModel::SUBSCRIBE_STATUS_YES,
                ], [
                    'user_id' => $this->userInfo['id'],
                    'live_id' => $liveId
                ]);
            } else {
                $res = $liveSubscribeModel->save([
                    'is_notify' => $isNotify,
                    'user_id' => $this->userInfo['id'],
                    'live_id' => $liveId,
                    'status' => LiveSubscribeModel::SUBSCRIBE_STATUS_YES,
                ]);
            }

            if ($res === false) throw new Exception('error');

            $res = Live::where(['id' => $liveId])->setInc('subscribe_num');

            if ($res === false) throw new Exception('error');


            Db::commit();
            return $this->_success();
        } catch (Exception $e) {

            Db::rollback();
            print_r($e->getMessage());

            $this->_error('LIVE_SUBSCRIBE_ERROR');
        }

        //通知主播逻辑(未实现)

    }

    /**
     * 取消订阅
     * @Author: wuyh
     * @Date: 2020/3/20 13:38
     */
    public function unSubscribe()
    {
        if (empty($this->userInfo['id'])) return $this->_error('TOKEN_TIME_OUT');
        $liveId = $this->_param('live_id');
        if (empty($liveId)) return $this->_error('PARAM_ERROR');

        Code::$code['LIVE_NOE_SUBSCRIBE'] = ['code' => '400', 'msg' => '您还没订阅过该直播'];
        Code::$code['LIVE_UNSUBSCRIBE_ERROR'] = ['code' => '400', 'msg' => '取消订阅失败'];

        $subscribe = $this->hasSubscribe($this->userInfo['id'], $liveId);

        if ($subscribe && $subscribe['status'] == LiveSubscribeModel::SUBSCRIBE_STATUS_NO) $this->_error('LIVE_NOE_SUBSCRIBE');

        try {

            Db::startTrans();
            $liveSubscribeModel = new LiveSubscribeModel();

            $res = $liveSubscribeModel->save([
                'status' => LiveSubscribeModel::SUBSCRIBE_STATUS_NO,
            ], [
                'user_id' => $this->userInfo['id'],
                'live_id' => $liveId
            ]);

            if ($res === false) throw new Exception('error');
            $res = Live::where(['id' => $liveId])->setDec('subscribe_num');
            if ($res === false) throw new Exception('error');

            Db::commit();
            return $this->_success();

        } catch (Exception $e) {
            Db::rollback();
            $this->_error('LIVE_UNSUBSCRIBE_ERROR');
        }

    }

    /**
     * 是否已经订阅过
     * @Author: wuyh
     * @Date: 2020/3/19 12:28
     */
    public function hasSubscribe($userId, $liveId)
    {
        $info = LiveSubscribeModel::where([
            "user_id" => $userId,
            'live_id' => $liveId,
        ])->find();

        return $info;
    }
}