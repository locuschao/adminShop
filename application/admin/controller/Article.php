<?php
/**
 * User: fangjinwei
 * Date: 2020/03/16 22:13
 * Desc: 文章管理
 */
namespace app\admin\controller;

use app\common\model\Article as ArticleModel;
use app\common\model\Message as MessageModel;
use library\Response;

class Article extends Base{

	protected $model;

	public function __construct()
	{
		parent::__construct();
		$this->model = new ArticleModel();
	}

	//文章列表
	public function list_view(){
		if(!$this->request->isAjax()) return $this->view->fetch();

		$page = (int)$this->request->get('page', 0);
		$pageSize = (int)$this->request->get('limit', 0);
		$ids = trim($this->request->get('ids',''));
		$addDate = $this->request->get('add_date','');
		$where['title'] = $this->request->get('title','');
		if (!empty($ids)) $where['id'] = ['in',explode(',',$ids)];
		if (!empty($addDate)){
			$date = explode('~',$addDate);
			$where['add_start_date'] = trim($date[0]);
			$where['add_end_date'] = trim($date[1]);
		}

		$count = $this->model->getCount($where);
		if(!empty($count)) {
			$list = $this->model->getList($where,$page,$pageSize);
		}
		Response::Json(0,"请求成功",$count,$list??[]);
	}

	//文章编辑
	public function edit(){
		$id = (int)$this->request->param('id');
		if(!$this->request->isAjax()){
			$info = [];
			if($id>0){
				$info = $this->model->where(['id'=>$id])->find();
				$info['start_time'] = date('Y-m-d H:i:s',$info['start_time']);
				$info['end_time'] = date('Y-m-d H:i:s',$info['end_time']);
				$this->view->assign('info',$info);
			}
			$this->view->assign('id',$id);
			$this->view->assign('info',$info);
			return $this->view->fetch();
		}

		$data['title'] = trim($this->request->post('title', ''));
		$data['keywords'] = trim($this->request->post('keywords', ''));
		$data['description'] = trim($this->request->post('description', ''));
		$data['nickname'] = $this->request->post('nickname', '');
		$data['user_id'] = (int)$this->request->post('user_id', 0);
		$data['goods_id'] = (int)$this->request->post('goods_id', 0);
		$data['article_ids'] = trim($this->request->post('article_ids', ''));
		$data['coupon_type'] = (int)$this->request->post('coupon_type', 0);
		$data['coupon_id'] = (int)$this->request->post('coupon_id', '');
		$data['recommend'] = (int)$this->request->post('recommend', 0);
		$data['recommend_img'] = $this->request->post('recommendImg', '');
		$data['source'] = $this->request->post('source', '');
		$data['thum'] = $this->request->post('thum', '');
		$data['content'] = $this->request->post('content', '');
		$data['sort'] = (int)$this->request->post('sort');
		$data['isshow'] = (int)$this->request->post('isshow');
		$data['mark'] = !empty($mark = $this->request->post('mark/a')) ? implode(',',$mark) : '';
		$data['start_time'] = $this->request->post('start_time','');
		$data['end_time'] = $this->request->post('end_time','');

		if(empty($data['description'])) $data['description'] = mb_substr( strip_tags( html_entity_decode($data['content']) ),0,80 );
		$data['start_time'] = !empty($data['start_time']) ? strtotime($data['start_time']) : time();
		$data['end_time'] = !empty($data['end_time']) ? strtotime($data['end_time']) : time();

		$validate = new \app\common\validate\Article();
		if($data['recommend']==1){
			$result = $validate->scene('save_recommend')->check($data);
		}else{
			$result = $validate->scene('save')->check($data);
		}

		if(!$result){
			$this->error($validate->getError());
		}

		if($id>0){
			if($this->model->save($data,['id'=>$id]) !== false){
				$this->success("修改成功");
			}
			$this->error('修改失败');
		}else{
			$data['add_time'] = time();
			if($lastInsertId = $this->model->insertGetId($data)){
				//发布动态消息
				$MessageModel = new MessageModel();
				$msgData = [
					'user_id' => $data['user_id'],
					'type' => $MessageModel::TYPE_ARTICLE,
					'title' => "您关注的{$data['nickname']}刚发布了一篇文章",
					'img' => $data['thum'],
					'content' => $data['title'],
					'jump_id' => $lastInsertId,
				];
				$MessageModel->add($msgData);
				$this->success("添加成功");
			}
			$this->error('添加失败');
		}
	}

	//删除文章
	public function del(){
		$id = (int)$this->request->get('id');

		if($this->model->where(['id'=>$id])->delete()){
			$this->success("删除成功");
		}
		$this->error('删除失败');
	}

}