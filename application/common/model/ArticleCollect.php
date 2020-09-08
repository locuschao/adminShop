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
namespace app\common\model;
use think\Model;

class ArticleCollect extends Model
{
    protected $name = 'article_collect';
    protected $resultSetType = 'collection';
    protected $autoWriteTimestamp = true;
    protected $createTime = 'create_at';
    protected $updateTime = 'update_at';


    const COLLECT_STATUS_YES = 1; //收藏
    const COLLECT_STATUS_NO = 2; //取消收藏

    //关联文章
    public function Article()
    {
        return $this->hasOne('Article', 'id', 'article_id');
    }
}