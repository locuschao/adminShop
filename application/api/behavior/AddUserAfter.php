<?php
// +----------------------------------------------------------------------
// | 新增用户后的操作
// +----------------------------------------------------------------------
// | Copyright (c) 2019-2020 rights reserved.
// +----------------------------------------------------------------------
// | Author: wuyh
// +----------------------------------------------------------------------
// | Date: 2020/3/23 19:05
// +----------------------------------------------------------------------

namespace app\api\behavior;
use think\controller;
use app\common\model\User;
use app\common\service\UserWallet;

class AddUserAfter extends controller
{
    public function _initialize()
    {

    }

    /**
     * 获取用户钱包信息
     * @param $params
     * @return bool
     * @Author: wuyh
     * @Date: 2020/3/23 19:43
     */
    public function run(&$params)
    {
        $userInfo = User::get($params['user_id']);
        if (empty($userInfo)) return false;
        $userWallet = new UserWallet();
        $ret = $userWallet->getUserWalletInfo($params['user_id']);

        return $ret;
    }
}