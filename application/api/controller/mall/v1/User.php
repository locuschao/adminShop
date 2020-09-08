<?php
/**
 * Created by PhpStorm.
 * User: cqingt
 * Date: 2017/12/6
 * Time: 11:04
 */

namespace app\api\controller\mall\v1;

use app\api\controller\mall\Base;
use app\common\service\GiftCodeService;
use library\Des;
use think\Config;
use app\common\model\UserFollow;
use library\Code;
use app\common\service\CouponService;

class User extends Base
{
    public function test()
    {
        $des = new Des();
        $token = $des->encrypt('oudaL5VItLBH-O57d7x1g8VFTmAQ' . "|1");
        $this->_response['data'] = [
            'md5Key' => Config::get('md5key'),
            'token' => $token,
        ];

        $this->_success('v1.User:test');
    }

    /**
     * 关注
     * @return \think\response
     * @Author: wuyh
     * @Date: 2020/3/24 10:09
     */
    public function follow()
    {
        if (empty($this->userInfo)) return $this->_error('TOKEN_TIME_OUT');

        Code::$code['FOLLOW_ERROR'] = ['code' => 400, 'msg' => '关注失败'];
        Code::$code['IS_FOLLOWED'] = ['code' => 400, 'msg' => '已经关注'];
        Code::$code['FOLLOW_SELF'] = ['code' => 400, 'msg' => '自己不能关注自己'];

        $followUserId = $this->_param('user_id');
        $userId = $this->userInfo['id'];

        if (empty($followUserId)) return $this->_error('PARAM_ERROR');
        $follow = $this->isFollow($followUserId);

        if ($follow && $follow['status'] == UserFollow::FOLLOW_YES) return $this->_error('IS_FOLLOWED');

        if ($userId == $followUserId) return $this->_error('FOLLOW_SELF');
        $userFollow = new UserFollow();

        if ($follow) {
            $res = $userFollow->save([
                'status' => UserFollow::FOLLOW_YES,
            ], [
                'user_id' => $userId,
                'user_id2' => $followUserId,

            ]);
        } else {
            $res = $userFollow->save([
                'user_id' => $userId,
                'user_id2' => $followUserId,
                'status' => UserFollow::FOLLOW_YES,
            ]);
        }

        if ($res === false) $this->_error('FOLLOW_ERROR');

        return $this->_success();
    }

    /**
     * 取消关注
     * @return \think\response
     * @Author: wuyh
     * @Date: 2020/3/24 10:14
     */
    public function unfollow()
    {
        Code::$code['UNFOLLOW_ERROR'] = ['code' => 400, 'msg' => '取消关注失败'];
        Code::$code['NO_FOUND_FOLLOW'] = ['code' => 400, 'msg' => '您还没有关注'];

        $followUserId = $this->_param('user_id');
        $userId = $this->userInfo['id'];

        if (empty($followUserId)) return $this->_error('PARAM_ERROR');
        $follow = $this->isFollow($followUserId);
        if (!$follow || $follow['status'] == UserFollow::FOLLOW_NO) return $this->_error('NO_FOUND_FOLLOW');

        $userFollow = new UserFollow();

        $res = $userFollow->save([
            'status' => UserFollow::FOLLOW_NO,
        ], [
            'user_id' => $userId,
            'user_id2' => $followUserId
        ]);

        if ($res === false) return $this->_error('UNFOLLOW_ERROR');

        return $this->_success();
    }

    /**
     * 是否关注
     * @param $userId
     * @return array|false|\PDOStatement|string|\think\Model
     * @Author: wuyh
     * @Date: 2020/3/24 9:55
     */
    public function isFollow($userId)
    {
        $info = UserFollow::where(['user_id2' => $userId])->find();

        return $info;
    }

    /**
     * 获取用户红包或优惠券
     * @return \think\response
     * @Author: wuyh
     * @Date: 2020/3/25 12:20
     */
    public function couponList()
    {
        if (empty($this->userInfo)) return $this->_error('TOKEN_TIME_OUT');
        $params = [
            'user_id' => $this->userInfo['id'],
            'status' => $this->_param('status'),
            'page' => $this->_param('page'),
            'limit' => $this->_param('limit'),
            'type' => $this->_param('type'),
        ];

        $ouponService = new CouponService();
        $data = $ouponService->getUserCouponList($params);
        $this->_response['data']['count'] =  $data['count'];
        $this->_response['data']['list'] = $data['list'];
        return $this->_success('v1.Mall.User:couponList');
    }

    /**
     * 用户兑换码
     * @return \think\response
     * @Author: wuyh
     * @Date: 2020/3/25 16:23
     */
    public function giftCodeList()
    {
        if (empty($this->userInfo)) return $this->_error('TOKEN_TIME_OUT');
        $params = [
            'user_id' => $this->userInfo['id'],
            'page' => $this->_param('page'),
            'limit' => $this->_param('limit'),
        ];

        $giftCodeService = new GiftCodeService();
        $data = $giftCodeService->userGiftCodeList($params);

        $this->_response['data']['count'] =  $data['count'];
        $this->_response['data']['list'] = $data['list'];
        return $this->_success('v1.Mall.User:giftCodeList');
    }

    public function redpackWithdraw()
    {
        if (empty($this->userInfo)) return $this->_error('TOKEN_TIME_OUT');
        if (empty($this->_param('coupon_id'))) return $this->_error('PARAM_ERROR');

        $params = [
            'coupon_id' => $this->_param('coupon_id'),
            'user_id' => $this->userInfo['id'],
        ];

        $couponService = new CouponService();
        $res = $couponService->redpackWithdraw($params);
        Code::$code['ERROR'] = ['code' => '400', 'msg' => $res['msg']];

        if ($res['code'] == 0) return $this->_error('ERROR');
        return $this->_success();
    }
}
