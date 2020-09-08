<?php
// +----------------------------------------------------------------------
// | 直播接口
// +----------------------------------------------------------------------
// | Author: Wuyh 2020-02-14
// +----------------------------------------------------------------------
namespace app\api\controller\live\v1;

use app\api\controller\live\Base;
use app\common\model\LiveGoods;
use app\common\model\Live AS LiveModel;
use app\common\model\UserLiveLog;
use app\Common\model\LiveSubscribe;
use library\Des;
use think\Db;
use think\Exception;
use think\Hook;
use library\Code;

class Live extends Base
{
    /**
     * 直播详情
     */
    public function liveDetail()
    {
        $id = intval($this->_param('id'));
        $liveDetail = LiveModel::get($id);

        if (empty($liveDetail)) $this->_error('DATA_NOT_EXIST');

        $this->_success('v1.Live:liveDetail');
    }

    /**
     * 直播列表
     * @throws \Exception
     */
    public function liveList()
    {
        $status = $this->_param('status');

        switch ($status) {
            case  LiveModel::LIVE_WAIT:
                $where = ['status' => LiveModel::LIVE_WAIT];
                break;
            case LiveModel::LIVE_BEGIN:
                $where = ['status' => LiveModel::LIVE_BEGIN];
                break;
            case LiveModel::LIVE_END:
                $where = ['status' => LiveModel::LIVE_END];
                break;
            default:
                $where = ['status' => LiveModel::LIVE_WAIT];
                break;
        }

        $liveList = LiveModel::where($where)
            ->with(['liveGoods.goods.images'])
            ->order('start_time asc')
            ->select()
            ->toArray();

        $res = [];
        if (!empty($liveList)) {
            foreach ($liveList as $val) {
                $res[] = [
                    'id' => $val['id'],
                    'title' => $val['title'],
                    'start_time' => $val['start_time'],
                    'anchor_id' => $val['anchor_id'],
                    'status' => $val['status'],
                    'memo' => $val['memo'],
                    'img_url' => $val['img_url'],
                    'has_video' => $val['has_video'],
                    'goods' => array_map(function ($value) {
                        return [
                            'goods_id' => $value['goods_id'],
                            'goods_name' => $value['goods']['goods_name'],
                            'shop_price' => $value['goods']['shop_price'],
                            'status' => $value['status'],
                            'images' => $value['goods']['images'][0]['src']
                        ];
                    }, $val['live_goods']),

                ];
            }
        }

        $this->_response['data']['list'] = $res;
        $this->_success('v1.Live:liveList');
    }

    /**
     * 开始直播
     * @throws \Exception
     */
    public function startLive()
    {
        if (empty($this->anchorInfo)) $this->_error('TOKEN_TIME_OUT');
        $userId = $this->anchorInfo['id'];
        $id = $this->_param('id');

        $time = time();
        $liveModel = new LiveModel();
        $res = $liveModel->validate('Live.start')->save([
            'status' => LiveModel::LIVE_BEGIN,
            'anchor_id' => intval($userId),
            'start_time' => $time,
        ],
            [
                'id' => $id
            ]);

        if ($res === false) $this->_error('DATA_ACTION_ERROR');
        //开播
        $data = ['method' => 'start_live', 'code' => 0, 'data' => ['live_id' => $id]];
        Hook::listen('live_action', $data);
        $this->_success();
    }

    //选择讲解的商品
    public function liveGoods()
    {
        $token = $this->_param('token');
        $goods_id = $this->_param('goods_id');
        $live_id = $this->_param('live_id');
        $des = new Des();
        $userInfo = $des->decrypt($token);
        if (empty($userInfo)) $this->_error('DATA_ACTION_ERROR');
        list($openId, $userId) = explode("|", $userInfo);

        //修改直播商品状态
        $liveGoodsModel = new LiveGoods();
        Db::startTrans();
        $res = $liveGoodsModel->update(array('status' => 0), array('live_id' => $live_id));
        if (false === $res) {
            Db::rollback();
            $this->_error('DATA_ACTION_ERROR');
        }
        $res = $liveGoodsModel->update(array('status' => 1), array('live_id' => $live_id, "goods_id" => $goods_id));
        if (false === $res) {
            Db::rollback();
            $this->_error('DATA_ACTION_ERROR');
        }
        Db::commit();
        $this->_success("v1.Live:liveGoods");
    }

    /**
     * 结束直播
     * @throws \Exception
     */
    public function stopLive()
    {
        $live_id = $this->_param('id');
        $userId = $this->userInfo['id'];

        $liveModel = new LiveModel();
        $res = $liveModel->validate('Live.stop')->save(
            [
                'status' => LiveModel::LIVE_END,
                'end_time' => time()
            ],
            [
                'id' => $live_id,
                'anchor_id' => $userId
            ]);
        if ($res === false) $this->_error('DATA_ACTION_ERROR');

        $liveGoodsModel = new LiveGoods();
        $res = $liveGoodsModel->update(array('status' => 0), array('live_id' => $live_id));
        if (false === $res) {
            $this->_error('DATA_ACTION_ERROR');
        }
        $liveInfo = $liveModel->where(array('id' => $live_id))->find();
        $liveInfo = empty($liveInfo) ? array() : $liveInfo->toArray();

        $this->_response['data']['list'] = $liveInfo;
        $this->_success("v1.Live:stopLive");
    }

    /**
     * 进入直播间
     * @Author: wuyh
     * @Date: 2020-02-14
     */
    public function intoLive()
    {
        $id = intval($this->_param('id'));
        $liveDetail = LiveModel::with(['liveGoods.goods.images', 'liveAnchor' => function ($query) {
            $query->field('id,nickname,username,head_url');
        }])->find($id);
        $info = [];

        if ($liveDetail) {
            $res = $liveDetail->toArray();
            $info = [
                'id' => $res['id'],
                'title' => $res['title'],
                'start_time' => $res['start_time'],
                'end_time' => $res['end_time'],
                'anchor_id' => $res['anchor_id'],
                'status' => $res['status'],
                'img_url' => $res['img_url'],
                'memo' => $res['memo'],
                'watch_num' => 1,
                'goods' => array_map(function ($value) {
                    return [
                        'goods_id' => $value['goods_id'],
                        'goods_name' => $value['goods']['goods_name'],
                        'shop_price' => $value['goods']['shop_price'],
                        'images' => $value['goods']['images'][0]['src'],
                        'status' => $value['status'],
                    ];
                }, $res['live_goods']),
                'anchor' => $res['live_anchor']
            ];

            if ($info['anchor'] && !$info['anchor']['head_url']) $info['anchor']['head_url'] = "http://test.weyu.image.zhiquhd.com/27006142a3a583b1fa8fd4a4587895.jpg";
        }

        $this->_response['data'] = $info;
        $this->_success('v1.Live:intoLive');
    }


    /**
     * 订阅直播间
     * @return \think\response
     * @Author: wuyh
     * @Date: 2020/3/30 22:47
     */
    public function subscribe()
    {
        $liveId = $this->_param('live_id');
        if (empty($this->userInfo) || empty($liveId)) return $this->_error('PARAM_ERROR');

        Code::$code['HAS_LIVE_SUBSCRIBE'] = ['code' => 400, 'msg' => '您已经订阅过该直播'];
        Code::$code['SUBSCRIBE_ERROR'] = ['code' => 400, 'msg' => '订阅失败'];

        $liveSubscribe = new LiveSubscribe();
        $res = $liveSubscribe->where(['user_id' => $this->userInfo['id']])->find();

        if ($res) $this->_error('HAS_LIVE_SUBSCRIBE');

        try {
            Db::startTrans();

            $liveSubscribeModel = new $liveSubscribe();
            $res = $liveSubscribeModel->save([
                'user_id' => $this->userInfo['id'],
                'live_id' => $liveId,
                'status' => LiveSubscribe::SUBSCRIBE_STATUS_YES,
            ]);

            if ($res === false) throw new Exception('error');
            $res = Live::where(['id' => $liveId])->setInc('subscribe_num');
            if ($res === false) throw new Exception('error');

            Db::commit();
            return $this->_success();
        } catch (Exception $e) {

            Db::rollback();

            $this->_error('SUBSCRIBE_ERROR');
        }

    }

    /**
     * 取消订阅直播间
     * @return \think\response
     * @Author: wuyh
     * @Date: 2020/3/30 22:47
     */
    public function unSubscribe()
    {
        $liveId = $this->_param('live_id');
        if (empty($this->userInfo) || empty($liveId)) return $this->_error('PARAM_ERROR');

        Code::$code['ARTICLE_NO_COLLECT'] = ['code' => 400, 'msg' => '您还没有订阅过该直播'];
        Code::$code['UNSUBSCRIBE_ERROR'] = ['code' => 400, 'msg' => '取消订阅失败'];

        $liveSubscribe = new LiveSubscribe();
        $res = $liveSubscribe->where(['user_id' => $this->userInfo['id']])->find();
        if (empty($res)) $this->_error('ARTICLE_NO_COLLECT');

        try {
            Db::startTrans();

            $liveSubscribeModel = new $liveSubscribe();
            $res = $liveSubscribeModel->save([
                'user_id' => $this->userInfo['id'],
                'live_id' => $liveId,
                'status' => LiveSubscribe::SUBSCRIBE_STATUS_YES,
            ]);

            if ($res === false) throw new Exception('error');
            $res = Live::where(['id' => $liveId])->setInc('subscribe_num');
            if ($res === false) throw new Exception('error');

            Db::commit();
            return $this->_success();
        } catch (Exception $e) {
            Db::rollback();
            $this->_error('UNSUBSCRIBE_ERROR');
        }
    }

}
