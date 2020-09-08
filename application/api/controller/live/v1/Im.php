<?php
// +----------------------------------------------------------------------
// | IM接口
// +----------------------------------------------------------------------
// | Author: Wuyh 2020-02-14
// +----------------------------------------------------------------------
namespace app\api\controller\live\v1;

use app\api\controller\live\Base;
use library\Des;
use library\TxIm;
use app\common\model\ImUsersig;

class Im extends Base
{
    /**
     * 获取UserSig
     * @throws \Exception
     */
    public function getUserSig()
    {
        $expire = 24*60*60;
        $token = $this->_param('token');
        $des = new Des();
        $userInfo = $des->decrypt($token);

        // if(empty($userInfo)) $this->_error('PARAM_ERROR');
        if ($userInfo) {
            list($openId,$userId) = explode("|",$userInfo);
        }

        //测试未传值先写死1 。避免体验账号超出
        $userId =  isset($userId) && !empty($userId) ? $userId  : 1;

        $TLSSigAPI = new TxIm(config('cfg')['TX_SDK_APP_ID'], config('cfg')['TX_SDK_APP_SECRET_KEY']);
        $usersig = $TLSSigAPI->genSig($userId, $expire);

        $imUsersig =  new ImUsersig([
            'usersig' => $usersig,
            'user_id' => $userId,
            'create_at' => time(),
        ]);

        $imUsersig->save();


        $this->_response['data'] = ['user_sig' => $usersig, 'user_id' => $userId];
        $this->_success('v1.Live:getUserSig');
    }
}
