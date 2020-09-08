<?php
// +----------------------------------------------------------------------
// | 文章收藏
// +----------------------------------------------------------------------
// | Copyright (c) 2019-2020 rights reserved.
// +----------------------------------------------------------------------
// | Author: wuyh
// +----------------------------------------------------------------------
// | Date: 2020/3/18 12:00
// +----------------------------------------------------------------------

namespace app\api\controller\mall\v1;

use app\api\controller\mall\Base;
use app\common\model\ArticleCollect AS ArticleCollectModel;
use app\common\model\Article;
use think\Exception;
use think\Db;
use library\Code;

class ArticleCollect extends Base
{
    /**
     * 收藏
     * @return \think\response
     * @Author: wuyh
     * @Date: 2020/3/19 12:42
     */
    function collect()
    {
        if (empty($this->userInfo)) $this->_error('PARAM_ERROR');
        $articleId = $this->_param('article_id');
        $article = Article::where(["id" => $articleId])->find();

        Code::$code['HAS_ARTICLE_COLLECT'] = ['code' => '400', 'msg' => '您已经收藏过该文章'];
        Code::$code['ARTICLE_NO_FOUND_ERROR'] = ['code' => '400', 'msg' => '文章不存在'];
        Code::$code['ARTICLE_COLLECT'] = ['code' => '400', 'msg' => '收藏失败'];

        if (!$article) $this->_error('ARTICLE_NO_FOUND_ERROR');

        $collect = $this->hasCollect($this->userInfo['id'], $articleId);
        if ($collect && $collect['status'] == ArticleCollectModel::COLLECT_STATUS_YES) return $this->_error('HAS_ARTICLE_COLLECT');

        try {
            Db::startTrans();

            $articleCollectModel = new ArticleCollectModel();

            if ($collect) {
                $res = $articleCollectModel->save([
                    'status' => ArticleCollectModel::COLLECT_STATUS_YES,
                ], [
                    'user_id' => $this->userInfo['id'],
                    'article_id' => $articleId
                ]);

            } else {
                $res = $articleCollectModel->save([
                    'user_id' => $this->userInfo['id'],
                    'article_id' => $articleId,
                    'status' => ArticleCollectModel::COLLECT_STATUS_YES,
                ]);
            }


            if ($res === false) throw new Exception('error');

            $res = Article::where(['id' => $articleId])->setInc('collect_num');

            if ($res === false) throw new Exception('error');

            Db::commit();
            return $this->_success();
        } catch (Exception $e) {

            Db::rollback();
            print_r($e->getMessage());

            $this->_error('ARTICLE_COLLECT');
        }
    }

    /**
     * 取消收藏
     * @Author: wuyh
     * @Date: 2020/3/19 12:47
     */
    function unCollect()
    {
        if (empty($this->userInfo)) $this->_error('PARAM_ERROR');
        $articleId = $this->_param('article_id');

        if (empty($articleId) || empty($this->userInfo)) $this->_error('PARAM_ERROR');

        Code::$code['ARTICLE_NO_COLLECT'] = ['code' => 400, 'msg' => '你还没有收载过该文章'];
        Code::$code['ARTICLE_UN_COLLECT_ERROR'] = ['code' => 400, 'msg' => '取消收藏失败'];

        $collect = $this->hasCollect($this->userInfo['id'], $articleId);

        if (!$collect || $collect['status'] == ArticleCollectModel::COLLECT_STATUS_NO) $this->_error('ARTICLE_NO_COLLECT');

        try {

            Db::startTrans();
            $articleCollectModel = new ArticleCollectModel();

            $res = $articleCollectModel->save([
                'status' => ArticleCollectModel::COLLECT_STATUS_NO,
            ], [
                'user_id' => $this->userInfo['id'],
                'article_id' => $articleId
            ]);

            if ($res === false) throw new Exception('error');

            $res = Article::where(['id' => $articleId])->setDec('collect_num');

            if ($res === false) throw new Exception('error');

            Db::commit();
            return $this->_success();


        } catch (Exception $e) {
            Db::rollback();
            $this->_error('ARTICLE_UN_COLLECT_ERROR');
        }
    }

    /**
     * 是否已经收藏过
     * @Author: wuyh
     * @Date: 2020/3/19 12:28
     */
    public function hasCollect($userId, $articleId)
    {
        $info = ArticleCollectModel::where([
            "user_id" => $userId,
            'article_id' => $articleId,
//            'status' => ArticleCollectModel::COLLECT_STATUS_YES
        ])->find();

        return $info;
    }
}