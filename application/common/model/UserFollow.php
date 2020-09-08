<?php
// +----------------------------------------------------------------------
// | 用户关注表
// +----------------------------------------------------------------------
// | Copyright (c) 2019-2020 rights reserved.
// +----------------------------------------------------------------------
// | Author: wuyh
// +----------------------------------------------------------------------
// | Date: 2020/3/21 14:55
// +----------------------------------------------------------------------
namespace app\common\model;
use think\Model;

class UserFollow extends Model
{
    protected $name = 'user_follow';
    protected $resultSetType = 'collection';
    protected $autoWriteTimestamp = true;
    protected $createTime = 'create_at';
    protected $updateTime = 'update_at';

    //关注状态
    const FOLLOW_YES = 1; //关注
    const FOLLOW_NO = 2; //取消关注

    /**
     * 关联关注的用户
     * @return \think\model\relation\HasOne
     * @Author: wuyh
     * @Date: 2020/3/24 14:01
     */
    public function Follow()
    {
        return $this->hasOne('User', 'id', 'user_id2')->bind([
            'follow_user_head_url' => 'head_url',
            'follow_user_nickname' => 'nickname'
        ]);

    }

    /**
     * 查询单条
     * @param array $condition
     * @return array
     */
    public function getDetail(array $condition){
        $where = $this->_condition($condition);
        $res = $this->where($where)->find();
        return !empty($res) ? $res->toArray() : [];
    }

    /**
     * 查询所有
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
     * 查询条件
     * @param array $condition
     * @return array
     */
    public function _condition(array $condition){
        $where = [];

        if (!empty($condition['user_id'])) $where['user_id'] = (int)$condition['user_id'];
        if (!empty($condition['user_id2'])) $where['user_id2'] = (int)$condition['user_id2'];

        return $where;
    }
}