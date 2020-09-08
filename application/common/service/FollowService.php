<?php
// +----------------------------------------------------------------------
// | 关注服务层
// +----------------------------------------------------------------------
// | Copyright (c) 2019-2020 rights reserved.
// +----------------------------------------------------------------------
// | Author: wuyh
// +----------------------------------------------------------------------
// | Date: 2020/3/23 20:21
// +----------------------------------------------------------------------

namespace app\common\service;

use app\common\model\Article;
use app\common\model\PayOrder;
use app\common\model\UserFollow;

class FollowService extends BaseService
{

    /**
     * 获取用户关注数量情况
     * @param $userId
     * @return array
     * @Author: wuyh
     * @Date: 2020/3/23 20:26
     */
    public function getFollowCountByUser($userId)
    {
        $data = [
            'follow' => 0,
            'fans' => 0,
            'total' => 0,
        ];

        if (empty($userId)) return $data;

        $userFollow = new UserFollow();
        $data['follow'] = $userFollow->where(['user_id' => $userId, 'status' => UserFollow::FOLLOW_YES])->count();
        $data['fans'] = $userFollow->where(['user_id2' => $userId, 'status' => UserFollow::FOLLOW_YES])->count();

        $data['total'] = array_sum($data);


        return $data;
    }

    /**
     * 关注的用户列表
     * @param $params
     * @return array
     * @Author: wuyh
     * @Date: 2020/3/24 11:44
     */
    public function userFollowList($params)
    {
        $data = [
            'count' => 0,
            'list' => []
        ];

        $userFollow = new UserFollow();
        $map = [
            'user_id' => $params['user_id'],
            'status' => UserFollow::FOLLOW_YES
        ];

        $data['count'] = $userFollow->where($map)->count();
        $list = $userFollow
            ->with('Follow')
                ->where($map)
            ->page($params['page'],
                $params['limit'])
            ->order('create_at')
            ->select();

        if ($list) {
            $list = $list->toArray();
            foreach ($list as &$v) {
                $v['article_count'] = Article::where(['author' => $v['user_id2'], 'status' => Article::STATUS_YES])->count();
            }

            $data['list'] = $list;
        }

        return $data;
    }
}