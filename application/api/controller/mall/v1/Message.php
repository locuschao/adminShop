<?php
/**
 * User: fangjinwei
 * Date: 2020/04/01 18:20
 * Desc: 消息
 */
namespace app\api\controller\mall\v1;

use app\api\controller\mall\Base;
use app\common\model\Message as MessageModel;
use app\common\model\UserFollow as UserFollowModel;
use library\Code;
use think\Db;

class Message extends Base
{

	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * 消息列表
	 */
	public function list(){
		if (empty($this->userInfo)) $this->_error('PARAM_ERROR');

		$user_id = $this->userInfo['id'];
		$type = (int)$this->_param('type',0);
		$page = (int)$this->_param('page',1);
		$pagesize = (int)$this->_param('pagesize',10);

		$MessageModel = new MessageModel();

		$where = $whereOr = [];
		switch($type){
			case 1: //订单消息
				$where = ['user_id' => $user_id, 'type' => $MessageModel::TYPE_ORDER];
				break;
			case 2: //福利消息
				$where = [
					'user_id' => $user_id,
					'type' => ['in',[$MessageModel::TYPE_COUPON,$MessageModel::TYPE_CASH]]
				];
				break;
			case 0: //全部消息
			case 3: //动态消息
				//查询关注的用户
				$UserFollowModel = new UserFollowModel();
				$where = ['user_id'=>$user_id,'status'=>$UserFollowModel::FOLLOW_YES];
				$list = $UserFollowModel->getAll($where);
				$followUsers = [];
				foreach($list as $key=>$val){
					array_push($followUsers,$val['user_id2']);
				}
				if($type == 3){
					$where = [
						'user_id' => ['in',$followUsers],
						'type' => ['in',[$MessageModel::TYPE_ARTICLE,$MessageModel::TYPE_LIVE]],
					];
				}else{
					$where = ['user_id'=>$user_id];
					$whereOr = ['user_id' => ['in',$followUsers]];
				}
				break;
			default:
				$this->_error('PARAM_ERROR');
		}

		$count = $MessageModel->getCount($where,$whereOr);
		if($count>0){
			$list = $MessageModel->getList($where,$page,$pagesize,$whereOr);
		}
		$hasnext = $count>$page*$pagesize?1:0;

		$this->_response['data']['count'] = $count;
		$this->_response['data']['has_next'] = $hasnext;
		$this->_response['data']['list'] = !empty($list) ? $list : [];

		$this->_success('v1.Mall.Message:list');
	}

	/**
	 * 阅读消息
	 */
	public function read(){
		if (empty($this->userInfo)) $this->_error('PARAM_ERROR');

		$id = (int)$this->_param('id',0);

		$MessageModel = new MessageModel();

		$MessageModel->save(['is_read'=>1],['id'=>$id]);

		$this->_response['data'] = '';

		$this->_success();
	}
}