<?php
// +----------------------------------------------------------------------
// | 基类
// +----------------------------------------------------------------------
// | Copyright (c) 2019-2020 rights reserved.
// +----------------------------------------------------------------------
// | Author: wuyh
// +----------------------------------------------------------------------
// | Date: 2020/4/2 10:18
// +----------------------------------------------------------------------

namespace app\swoole\event;

use app\common\model\LiveUser;
use app\common\model\User;
use library\Des;
use think\Cache;
use think\Config;

class Base
{
    /**
     * Redis
     * @var
     */
    protected $redis;

    /**
     * 用户
     * @var
     */
    protected $userInfo;

    public function __construct()
    {
        $this->redis = Cache::connect(Config::get('cache.redis'));
    }

    /**
     * 用户信息
     * @param $data
     * @return array|false|\PDOStatement|string|\think\Model
     * @Author: wuyh
     * @Date: 2020/3/31 14:56
     */
    public function getUserInfo($data)
    {
        $res = $this->checkParams('token', $data);
        if ($res['code'] == 0) return $res;
        $token = $data['token'];
        $des = new Des();
        $info = $des->decrypt($token);

        if (empty($userInfo)) return ['code' => 0, 'msg' => '用户ID未找到'];
        $arr = explode("|", $info);
        if (count($arr) < 2) return ['code' => 0, 'msg' => '未知错误 -1001'];

        $model = new User();
        $userInfo = $model->where(['id' => $arr[1]])->field('password,openid,unionid', true)->find();
        if (empty($userInfo)) return ['code' => 0, 'msg' => '用户信息不存在'];

        $userInfo = $userInfo->toArray();
        $userInfo['user_role'] = \app\common\model\Live::LIVE_USER_NORMAL;

        return ['code' => 1, 'msg' => 'success', 'data' => $userInfo];
    }

    /**
     * 获取主播信息
     * @param $data
     * @return array|false|\PDOStatement|string|\think\Model
     * @Author: wuyh
     * @Date: 2020/3/31 14:58
     */
    public function getAnchorInfo($data)
    {
        $res = $this->checkParams('token', $data);
        if ($res['code'] == 0) return $res;

        $token = $data['token'];
        $des = new Des();
        $info = $des->decrypt($token);
        if (empty($info)) return ['code' => 0, 'msg' => '用户ID未找到'];

        $arr = explode("|", $info);
        if (count($arr) < 2) return ['code' => 0, 'msg' => '未知错误 -1001'];


        $model = new LiveUser();
        $userInfo = $model->where(['id' => $arr[1]])->field('password,openid,unionid', true)->find();

        if (empty($userInfo)) return ['code' => 0, 'msg' => '用户信息不存在'];

        $userInfo = $userInfo->toArray();
        $userInfo['user_role'] = \app\common\model\Live::LIVE_USER_MANAGE;
        return ['code' => 1, 'msg' => 'success', 'data' => $userInfo];
    }

    /**
     * 检查参数
     * @param string $field
     * @param $data
     * @return array
     * @Author: wuyh
     * @Date: 2020/3/31 15:52
     */
    public function checkParams($field = '', $data)
    {
        $need = [];   //必填字段
        if ($field) {
            $need = explode(',', $field);
            foreach ($need as $val) {
                if (!isset($data[$val]) || is_null($data[$val]) || $data[$val] === '') {
                    return ['code' => 0, 'msg' => '参数' . $val . '不能为空！'];
                }
            }
        }

        return ['code' => 1, 'msg' => '成功'];
    }

    /**
     * 验证签名
     * @param $data
     * @return bool
     * @Author: wuyh
     * @Date: 2020/4/2 11:11
     */
    public function checkSign($data)
    {
        return false;
    }
}