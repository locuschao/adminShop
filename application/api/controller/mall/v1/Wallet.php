<?php
// +----------------------------------------------------------------------
// | 钱包
// +----------------------------------------------------------------------
// | Copyright (c) 2019-2020 rights reserved.
// +----------------------------------------------------------------------
// | Author: wuyh
// +----------------------------------------------------------------------
// | Date: 2020/3/24 10:46
// +----------------------------------------------------------------------
namespace app\api\controller\mall\v1;

use app\api\controller\mall\Base;
use app\common\service\UserWalletService;
use library\Code;

class Wallet extends Base
{
    /**
     * 提现申请
     * @return \think\response
     * @Author: wuyh
     * @Date: 2020/3/24 17:04
     */
    public function cashWithdraw()
    {
        if (empty($this->userInfo)) $this->_error('TOKEN_TIME_OUT');
        //是否需要启用安全密码才能提现
        $userId = $this->userInfo['id'];
        $money = max($this->_param('money'), 0);
        $withdrawWay = $this->_param('withdraw_way');

        if (empty($money) || empty($userId)) return $this->_error('PARAM_NOT_EMPTY');
        $userWalletService = new UserWalletService();

        $params = [
            'user_id' => $userId,
            'money' => $money,
            'withdraw_way' => $withdrawWay,
        ];

        $res = $userWalletService->cashWithdraw($params);

        if ($res['code'] == 0) {
            Code::$code['ERROR'] = ['code' => '400', 'msg' => $res['msg']];
            $this->_error('ERROR');
        }

        return $this->_success();
    }

    /**
     * 提现记录
     * @return \think\response
     * @Author: wuyh
     * @Date: 2020/3/25 17:06
     */
    public function withdrawLog()
    {
        if (empty($this->userInfo)) $this->_error('TOKEN_TIME_OUT');

        $userId = $this->userInfo['id'];
        $userWalletService = new UserWalletService();

        $params = [
            'page' => $this->_param('page'),
            'limit' => $this->_param('limit'),
            'user_id' => $userId,
        ];

        $data = $userWalletService->walletLogList($params);
        $this->_response['count'] = $data['count'];
        $this->_response['data']['list'] = $data['list'];
        return  $this->_success('v1.Mall.Wallet:withdrawLog');
    }
}