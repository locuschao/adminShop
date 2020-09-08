<?php
// +----------------------------------------------------------------------
// | 收藏服务层
// +----------------------------------------------------------------------
// | Copyright (c) 2019-2020 rights reserved.
// +----------------------------------------------------------------------
// | Author: wuyh
// +----------------------------------------------------------------------
// | Date: 2020/3/23 20:10
// +----------------------------------------------------------------------

namespace app\common\service;

use app\common\model\GoodsCollect;
use app\common\model\ArticleCollect;

class CollectionService extends BaseService
{
    /**
     * 获取用户收藏的商品和文章数
     * @param $userId
     * @return array
     * @Author: wuyh
     * @Date: 2020/3/23 20:16
     */
    public function getCollectCountByUser($userId)
    {
        $data = [
            'goods' => '0',
            'article' => '0',
            'total' => '0'
        ];

        if (empty($userId)) return $data;

        $goodsCollect = new GoodsCollect();
        $articleCollect = new ArticleCollect();

        $data['goods'] = $goodsCollect->where(['user_id' => $userId, 'status' => GoodsCollect::COLLECT_STATUS_YES])->count();
        $data['article'] = $articleCollect->where(['user_id' => $userId, 'status' => ArticleCollect::COLLECT_STATUS_YES])->count();

        $data['total'] = array_sum($data);

        return $data;
    }

    /**
     * 用户收藏的商品
     * @param $params
     * @return array
     * @Author: wuyh
     * @Date: 2020/3/25 15:53
     */
    public function userGoodsCollectList($params)
    {
        $res = [
            'count' => 0,
            'list' => []
        ];

        if (!isset($params['user_id']) || empty($params['user_id'])) return $res;

        $goodsCollect = new GoodsCollect();

        $map = [
            'user_id' => $params['user_id'],
            'status' => GoodsCollect::COLLECT_STATUS_YES
        ];

        $res['count'] = $goodsCollect->where($map)->count();
        $list = $goodsCollect->where($map)
            ->with('Goods.images')
            ->page($params['page'], $params['limit'])
            ->order('create_at DESC')
            ->select();

        if (!empty($list)) {
            $list = $list->toArray();
            foreach ($list as $k => &$v) {
                $v['goods']['cover_image'] = '';
                if (empty($v['goods']['images'])) continue;

                foreach ($v['goods']['images'] as $image) {
                    if ($image['is_seleted'] == 1) {
                        $v['goods']['cover_image'] = $image['src'];
                        break;
                    }
                }
                unset($v['goods']['images']);
            }
            $res['list'] = $list;
        }

        return $res;
    }

    /**
     * 用收藏的文章
     * @param $params
     * @return array
     * @Author: wuyh
     * @Date: 2020/3/25 16:00
     */
    public function userArticlesCollectList($params)
    {
        $res = [
            'count' => 0,
            'list' => []
        ];

        if (!isset($params['user_id']) || empty($params['user_id'])) return $res;

        $articleCollect = new ArticleCollect();
        $map = [
            'user_id' => $params['user_id'],
            'status' => ArticleCollect::COLLECT_STATUS_YES
        ];

        $res['count'] = $articleCollect->where($map)->count();
        $list = $articleCollect->where($map)
            ->with('Article')
            ->page($params['page'], $params['limit'])
            ->order('create_at DESC')
            ->select();

        if (!empty($list))   $res['list'] = $list->toArray();

        return $res;
    }
}
