<?php
//用户表
namespace app\common\model;
class User extends Base
{
    protected $name = 'user';
    protected $autoWriteTimestamp = true;
    protected $createTime = 'reg_time';
    protected $updateTime = 'login_time';

    public function fetchCount(array $condition)
    {
        $count = $this->where($condition)->count();

        return $count;
    }

    public function fetchList(array $condition, $offset, $limit = 10)
    {
        $list = $this->where($condition)->order('id desc')->limit("$offset,$limit")->select();
        if (empty($list)) {
            return array();
        }

        return $list->toArray();
    }

}
