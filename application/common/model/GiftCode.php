<?php
/**
 * [充换码]
 */
namespace app\common\model;
class GiftCode extends Base{
    protected $name = 'gift_code';
    //获取数量
    public function fetchCount(array $condition) {
        $count = $this
            ->where($condition)
            ->count();
        return $count;
    }

    //获取列表
    public function fetchList(array $condition, $offset,$limit = 10) {
        $list = $this
            ->where($condition)
            ->order('id desc')
            ->limit("$offset,$limit")
            ->select();
        return empty($list)?array():$list->toArray();
    }

    //获取详情
    public function getDetail(array $condition){
        $list = $this->where($condition)->find();
        return empty($list)?array():$list->toArray();
    }

    /**
     * 关联兑换活动
     * @return \think\model\relation\HasOne
     * @Author: wuyh
     * @Date: 2020/3/25 16:21
     */
    public function GitActivity()
    {
        return $this->hasOne('GiftActivity','id', 'gid')->bind([
            'title' => 'title'
        ]);
    }


}