<?php
namespace app\common\model;
use app\common\validate\GoodsBrand as GoodBrandValidate;
class Brand extends Base
{
    // 表名
    protected $name = 'brand';

    //获取数量
    public function fetchCount(array $condition) {
        $count = $this->where($condition)->count();
        return $count;
    }

    //获取列表
    public function fetchList(array $condition, $offset,$limit = 10) {
        $list = $this->where($condition)->order('id desc')->limit("$offset,$limit")->select();
        return empty($list)?array():$list->toArray();
    }

    //获取详情
    public function getDetail(array $condition){
        $list = $this->where($condition)->find();
        return empty($list)?array():$list->toArray();
    }

    /**
     * 新增或更新
     * @param $params
     * @return array
     * @throws \Exception
     */
    public function toAdd($params)
    {
        $validate = new GoodBrandValidate();
        $result = $validate->scene('save')->check($params);

        if (!$result) return['code' => 0, 'msg' => $validate->getError()];

        $ret = (isset($params['id']) && !empty($params['id'])) ? $this->allowField(true)->save($params, $params['id']) : $this->allowField(true)->save($params);
        if (!$ret) return['code' => 0, 'msg' => $this->getError()];
        return ['code' => 1, 'msg' => 'SUCCESS'];
    }


    /**
     * @param $where
     * @return int
     * 删除
     */
    public function drop($where)
    {
        return $this->where($where)->delete();
    }
}