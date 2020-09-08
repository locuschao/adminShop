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
        // if ($userInfo) {
        //     list($openId,$userId) = explode("|",$userInfo);
        // }

        // $userId =  isset($userId) && !empty($userId) ? $userId  : time() + rand(1000 , 9999);
        // 
        $userId = 'yuanhang';

        $TLSSigAPI = new TxIm(config('cfg')['TX_SDK_APP_ID'], config('cfg')['TX_SDK_APP_SECRET_KEY']);
        $usersig = $TLSSigAPI->genSig($userId, $expire);

        $random = rand(0, 4294967295);

        $url = "https://console.tim.qq.com/v4/im_open_login_svc/account_delete?sdkappid=1400321470&identifier=yuanhang&usersig={$usersig}&random={$random}&contenttype=json";

        echo $url;exit;

        // $imUsersig =  new ImUsersig([
        //     'usersig' => $usersig,
        //     'user_id' => $userId,
        //     'create_at' => time(),
        // ]);

        // $imUsersig->save();


        $this->_response['data'] = ['user_sig' => $usersig, 'user_id' => $userId];
        $this->_success('v1.Live:getUserSig');
    }
}
