<?php
/**
 * User: fangjinwei
 * Date: 2020/03/19 17:46
 * Desc: 文章model
 */
namespace app\common\model;

class Article extends Base
{
	protected $name = 'article';

	protected $resultSetType = 'collection';

	const SHOW = 1; // 状态：正常

	const NOT_SHOW = 0; //状态：不显示

	const RECOMMEND = 1; //推荐

	const IS_SHOW = [
		self::NOT_SHOW => '不显示',
		self::SHOW => '正常',
	];


	/**
	 * 获取列表
	 * @param array $condition
	 * @param int $page
	 * @param int $pageSize
	 * @return array
	 */
	public function getList(array $condition,int $page,int $pageSize){
		$where = $this->_condition($condition);

		if($page<1 || $pageSize<1) return [];
		$offset = $pageSize * ($page - 1);

		$res = $this->where($where)->order('`sort` desc,id desc')->limit($offset,$pageSize)->select();

		return !empty($res) ? $res->toArray() : [];
	}

	/**
	 * 获取总数
	 * @param array $condition
	 * @return int
	 */
	public function getCount(array $condition){
		$where = $this->_condition($condition);
		$count = $this->where($where)->count();
		return (int)$count;
	}

	/**
	 * 获取所有文章
	 * @param array $condition
	 * @return array
	 */
	public function getAll(array $condition){
		$where = $this->_condition($condition);

		if(empty($where)) return [];

		$res = $this->where($where)->select();

		return !empty($res) ? $res->toArray() : [];
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
	 * 获取列表
	 * @param int $user_id
	 * @param array $followUsers
	 * @param int $page
	 * @param int $pageSize
	 * @return array
	 */
	public function getListAndCollect(int $user_id,array $followUsers,int $page,int $pageSize){
		if($page<1 || $pageSize<1) return [];
		$offset = $pageSize * ($page - 1);

		$where = [
			'a.isshow' => self::SHOW,
			'a.start_time' => ['elt',time()],
			'a.end_time' => ['gt',time()],
		];
		if(!empty($followUsers)) $where['a.user_id'] = ['in',$followUsers];

		$res = $this->alias('a')
			->field('a.*,IFNULL(c.status ,0) AS is_collect')
			->join('cc_article_collect c','a.id=c.article_id AND c.status=1 AND c.user_id='.$user_id,'left')
			->where($where)
			->order('a.`sort` desc,a.id desc')
			->limit($offset,$pageSize)
			->select();

		return !empty($res) ? $res->toArray() : [];
	}

	/**
	 * 查询文章关联代金券
	 * @param int $article_id
	 * @return array
	 */
	public function getArticleCoupon(int $article_id){
		$res = $this->alias('a')
			->field('a.id,a.coupon_id,a.title,c.title as coupon_title,c.type as coupon_type, c.full_money,c.money,c.day')
			->join('cc_coupon c','a.coupon_id=c.id','left')
			->where(['a.id'=>$article_id,'c.status'=>1])
			->find();
		return !empty($res) ? $res->toArray() : [];
	}

	/**
	 * 查询文章关联商品及是否收藏
	 * @param int $article_id
	 * @param int $user_id
	 * @return array
	 */
	public function getArticleGoodsAndCollect(int $article_id,int $user_id){
		$res = $this->alias('a')
			->field('a.id,a.article_ids,a.title,a.user_id,a.nickname,a.thum,a.add_time,a.content,g.goods_id,g.goods_name,g.market_price,g.shop_price,g.is_hot,g.is_new,gi.src,IFNULL(c.status ,0) AS is_collect')
			->join('cc_goods g','a.goods_id=g.goods_id AND `g`.`check`=1','left')
			->join('cc_goods_image gi','a.goods_id=gi.goods_id','left')
			->join('cc_article_collect c','a.id=c.article_id AND c.status=1 AND c.user_id='.$user_id,'left')
			->where(['a.id'=>$article_id])
			->find();
		return !empty($res) ? $res->toArray() : [];
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
		if(!empty($condition['isshow'])) $where['isshow'] = $condition['isshow'];
		if(!empty($condition['recommend'])) $where['recommend'] = $condition['recommend'];
		if(!empty($condition['start_time'])) $where['start_time'] = $condition['start_time'];
		if(!empty($condition['end_time'])) $where['end_time'] = $condition['end_time'];
		if(!empty($condition['add_start_date']) && !empty($condition['add_end_date'])){
			$where['add_time'] = [
				['egt',strtotime($condition['add_start_date'])],
				['lt',strtotime($condition['add_end_date'])+86400]
			];
		}
		if(!empty($condition['title'])) $where['title'] = ['like',"%{$condition['title']}%"];
		return $where;
	}
}