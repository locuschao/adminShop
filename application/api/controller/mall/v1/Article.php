<?php
/**
 * User: fangjinwei
 * Date: 2020/03/23 10:59
 * Desc: 文章
 */
namespace app\api\controller\mall\v1;

use app\api\controller\mall\Base;
use app\common\model\Coupon as CouponModel;
use app\common\model\User as UserModel;
use app\common\model\Article as ArticleModel;
//use app\common\model\ArticleComment as ArticleCommentModel;
use app\common\model\UserCoupon as UserCouponModel;
use app\common\model\UserCouponLog as UserCouponLogModel;
use app\common\model\UserFollow as UserFollowModel;
use library\Code;
use think\Db;

class Article extends Base
{

	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * 首页bannel
	 */
	public function bannel(){
		$ArticleModel = new ArticleModel();

		$where = [
			'isshow'=>$ArticleModel::SHOW,
			'recommend'=>$ArticleModel::RECOMMEND,
			'start_time' => ['elt',time()],
			'end_time' => ['gt',time()],
		];
		$list = $ArticleModel->getList($where,1,10);

		$this->_response['data']['list'] = $list;

		$this->_success('v1.Mall.Article:bannel');
	}

	/**
	 * 文章列表
	 */
	public function list(){
		if (empty($this->userInfo)) $this->_error('PARAM_ERROR');

		$page = (int)$this->_param('page',1);
		$pagesize = (int)$this->_param('pagesize',10);
		$user_id = (int)$this->userInfo['id'];

		$ArticleModel = new ArticleModel();

		$where = [
			'isshow'=>ArticleModel::SHOW,
			'start_time' => ['elt',time()],
			'end_time' => ['gt',time()],
		];
		$count = $ArticleModel->getCount($where);
		if($count>0) $list = $ArticleModel->getListAndCollect($user_id,[],$page,$pagesize);
		$hasnext = $count>$page*$pagesize?1:0;

		$this->_response['data']['count'] = $count;
		$this->_response['data']['has_next'] = $hasnext;
		$this->_response['data']['list'] = !empty($list) ? $list : [];

		$this->_success('v1.Mall.Article:list');
	}

	/**
	 * 关注的用户发布的文章列表
	 */
	public function followArticles(){
		if (empty($this->userInfo)) $this->_error('PARAM_ERROR');

		$page = (int)$this->_param('page',1);
		$pagesize = (int)$this->_param('pagesize',10);
		$user_id = (int)$this->userInfo['id'];

		$UserFollowModel = new UserFollowModel();
		$where = ['user_id'=>$user_id,'status'=>$UserFollowModel::FOLLOW_YES];
		$list = $UserFollowModel->getAll($where);

		$followUsers = [];
		foreach($list as $key=>$val){
			array_push($followUsers,$val['user_id2']);
		}

		if(!empty($followUsers)){
			$ArticleModel = new ArticleModel();
			$where = [
				'isshow' => ArticleModel::SHOW,
				'user_id' => ['in',$followUsers],
				'start_time' => ['elt',time()],
				'end_time' => ['gt',time()],
			];
			$count = $ArticleModel->getCount($where);
			if($count>0) $list = $ArticleModel->getListAndCollect($user_id,$followUsers,$page,$pagesize);
			$hasnext = $count>$page*$pagesize?1:0;
		}

		$this->_response['data']['count'] = $count ?? 0;
		$this->_response['data']['has_next'] = $hasnext ?? 0;
		$this->_response['data']['list'] = !empty($list) ? $list : [];

		$this->_success('v1.Mall.Article:list');
	}

	/**
	 * 文章详情,关联商品，关联文章
	 */
	public function detail(){
		if (empty($this->userInfo)) $this->_error('PARAM_ERROR');

		$user_id = (int)$this->userInfo['id'];
		$id = (int)$this->_param('id',0);

		$ArticleModel = new ArticleModel();
		$info = $ArticleModel->getArticleGoodsAndCollect($id,$user_id);

		if(!empty($info)){
			//查询发布文章的用户是否己被关注
			$UserFollowModel = new UserFollowModel();
			$userFollow = $UserFollowModel->getDetail(['user_id'=>$user_id,'user_id2'=>$info['user_id']]);

			$article = [
				'id' => $info['id'],
				'title' => $info['title'],
				'nickname' => $info['nickname'],
				'user_id' => $info['user_id'],
				'thum' => $info['thum'],
				'add_time' => date('Y-m-d H:i:s',$info['add_time']),
				'content' => stripcslashes($info['content']),
				'is_follow' => !empty($userFollow) ? 1 : 0,
			];

			//关联文章
			if (!empty($info['article_ids'])){
				$list = $ArticleModel->getAll(['id'=>['in',explode(',',$info['article_ids'])]]);
				foreach($list as $key=>$val){
					$article_relation[$key]['id'] = $val['id'];
					$article_relation[$key]['title'] = $val['title'];
					$article_relation[$key]['thum'] = $val['thum'];
					$article_relation[$key]['description'] = $val['description'];
				}
			}

			//修改文章的阅读数
			$ArticleModel->where(['id'=>$id])->setInc('read_num');
		}
		if(!empty($info['goods_id'])){
			$goods =[
				'goods_id' => $info['goods_id'],
				'goods_name' => $info['goods_name'],
				'market_price' => $info['market_price'],
				'shop_price' => $info['shop_price'],
				'is_hot' => $info['is_hot'],
				'is_new' => $info['is_new'],
				'src' => $info['src'],
			];
		}

		$this->_response['data'] = [
			'article' => !empty($article) ? $article : [],
			'goods' => !empty($goods) ? $goods : [],
			'article_relation' => !empty($article_relation) ? $article_relation : [],
		];

		$this->_success('v1.Mall.Article:detail');
	}

	/**
	 * 文章分享,分享完成发放优惠券
	 */
	public function shared(){
		if (empty($this->userInfo)) $this->_error('PARAM_ERROR');

		$user_id = (int)$this->userInfo['id'];
		$article_id = (int)$this->_param('article_id',0);

		//查询是否有优惠券
		$ArticleModel = new ArticleModel();
		$info = $ArticleModel->getArticleCoupon($article_id);

		$this->_response['data'] = '';
		if(empty($info)) $this->_success();

		//查询是否己经领取优惠券
		$UserCouponLogModel = new UserCouponLogModel();
		$res = $UserCouponLogModel->getDetail(['user_id'=>$user_id,'from'=>$UserCouponLogModel::ARTICLE,'ext'=>$article_id]);
		if(!empty($res)) $this->_success();

		Code::$code['ARTICLE_SHARED_COUPON_ERROR'] = ['code' => '400', 'msg' => '发放优惠券失败'];

		$UserCouponModel = new UserCouponModel();

		Db::startTrans();
		$res = $UserCouponModel->addCoupon($UserCouponModel::SHARE_TYPE,$info['coupon_id'],$user_id,$info['day'],$info['title']);
		if(empty($res)) {
			Db::rollback();
			$this->_error('ARTICLE_SHARED_COUPON_ERROR');
		}
		$res = $UserCouponLogModel->addCouponLog($UserCouponLogModel::ARTICLE,$UserCouponLogModel::SHARE_TYPE,$info['coupon_id'],$user_id,$info['id']);
		if(empty($res)) {
			Db::rollback();
			$this->_error('ARTICLE_SHARED_COUPON_ERROR');
		}

		//发放优惠券消息


		DB::commit();

		$this->_response['data'] = $info;
		$this->_success('v1.Mall.Article:shared');

		$this->_error('ARTICLE_SHARED_COUPON_ERROR');
	}

}