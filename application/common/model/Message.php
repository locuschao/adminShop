<?php
/**
 * User: fangjinwei
 * Date: 2020/04/01 17:15
 * Desc: 消息model
 */
namespace app\common\model;

class Message extends Base
{
	protected $name = 'message';

	protected $resultSetType = 'collection';

	/**
	 * 消息类别
	 */
	const TYPE_ORDER = 1; //订单

	const TYPE_COUPON = 2; //代金券福利

	const TYPE_CASH = 3; //红包福利

	const TYPE_ARTICLE = 4; //互动文章

	const TYPE_LIVE = 5; //互动直播


	const MSG_TYPE = [
		self::TYPE_ORDER => '订单',
		self::TYPE_COUPON => '代金券福利',
		self::TYPE_CASH => '红包福利',
		self::TYPE_ARTICLE => '互动文章',
		self::TYPE_LIVE => '互动直播',
	];


	/**
	 * 获取列表
	 * @param array $condition
	 * @param array $conditionOr
	 * @param int $page
	 * @param int $pageSize
	 * @return array
	 */
	public function getList(array $condition,int $page,int $pageSize,array $conditionOr){
		$where = $this->_condition($condition);

		if($page<1 || $pageSize<1) return [];
		$offset = $pageSize * ($page - 1);

		if(!empty($conditionOr)){
			$res = $this->where($where)->whereOr($conditionOr)->order('id desc')->limit($offset,$pageSize)->select();
		}else{
			$res = $this->where($where)->order('id desc')->limit($offset,$pageSize)->select();
		}

		return !empty($res) ? $res->toArray() : [];
	}

	/**
	 * 获取总数
	 * @param array $condition
	 * @param array $conditionOr
	 * @return int
	 */
	public function getCount(array $condition,array $conditionOr){
		$where = $this->_condition($condition);
		if(!empty($conditionOr)){
			$count = $this->where($where)->whereOr($conditionOr)->count();
		}else{
			$count = $this->where($where)->count();
		}
		return (int)$count;
	}

	/**
	 * 查询单条
	 * @param array $condition
	 * @param array $order
	 * @return array
	 */
	public function getDetail(array $condition,array $order=[]){
		$res = $this->where($condition)->find();
		if(!empty($order)){
			$res = $this->where($condition)->order($order)->find();
		}
		return !empty($res) ? $res->toArray() : [];
	}


	/**
	 * 添加
	 * @param array $data
	 * @return bool|int
	 */
	public function add(array $data){
		if(empty($data['user_id'])) return false;
		if(!in_array($data['type'],array_keys(self::MSG_TYPE))) return false;
		if(empty($data['title'])) return false;
		if(empty($data['content'])) return false;

		$data['create_time'] = time();
		$insertId = $this->insertGetId($data);
		return !empty($insertId) ? (int)$insertId : 0;
	}

	/**
	 * 查询条件
	 * @param array $condition
	 * @return array
	 */
	public function _condition(array $condition){
		$where = [];
		if(!empty($condition['id'])) $where['id'] = $condition['id'];
		if(!empty($condition['user_id'])) $where['user_id'] = $condition['user_id'];
		if(!empty($condition['type'])) $where['type'] = $condition['type'];
		/*if(!empty($condition['create_time']) && !empty($condition['create_time'])){
			$where['create_time'] = [
				['egt',strtotime($condition['create_time'])],
				['lt',strtotime($condition['create_time'])+86400]
			];
		}*/
		return $where;
	}
}