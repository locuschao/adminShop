<?php
/**
 * [用户进入直播间活动]
 */
namespace app\api\controller\mall\v1;
use app\api\controller\mall\Base;
use app\common\service\LiveActivityService;
class LiveActivity extends Base{

    //单次互动列表
    public function activity(){
        $live_id = (int)$this->_param('live_id');
        $user_id =  empty($this->userInfo)?0:$this->userInfo['id'];
        if(empty($user_id)){
            $this->_error('TOKEN_TIME_OUT');
        }
        $liveActivityService = new LiveActivityService();
        $data = $liveActivityService->getLiveSingleList($live_id,$user_id);
        $this->_response['data']['list'] = $data;
        $this->_success('v1.liveActivity:activity');
    }

    //单次互动
    public function getSingleAward(){
        $live_id = (int)$this->_param('live_id');
        $type = (int)$this->_param('type');
        $id = (int)$this->_param('id');
        $action = $this->_param('action');
        $user_id = empty($this->userInfo)?0:$this->userInfo['id'];
        if(empty($user_id)){
            $this->_error('TOKEN_TIME_OUT');
        }
        $liveActivityService = new LiveActivityService();
        $data = $liveActivityService->getSingleAward($action,$live_id,$type,$id,$user_id);
        if(empty($data)){
            $this->_error('SINGLE_ACTIVITY');
        }
        $this->_response['data']['list'] = $data;
        $this->_success('v1.liveActivity:getSingleAward');
    }
}