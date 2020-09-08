<?php
// +----------------------------------------------------------------------
// | 广告位值
// +----------------------------------------------------------------------
// | Author: pc 2020-02-06
// +----------------------------------------------------------------------
namespace app\common\model;
use app\common\validate\AdPos AS AdPosValidate;

class AdPosition extends Base{
    protected $name = 'ad_position';
    protected $resultSetType = 'collection';

    //获取数量
    public function fetchCount(array $condition) {
        $count = $this->where($condition)->count();

        return $count;
    }

    //获取多条记录
    public function fetchList(array $condition, $offset,$limit = 10) {
        $list = $this->where($condition)->order('id desc')->limit("$offset,$limit")->select();
        if(empty($list)){
            return array();
        }

        return $list->toArray();
    }

    //获取单条
    public function getDetail(array $condition){
        $list = $this->where( $condition)->find();
        if(empty($list)){
            return array();
        }

        return $list->toArray();
    }

    /**
     * 新增或更新
     * @param $params
     * @return array
     * @throws \Exception
     */
    public function toAdd($params)
    {
        $validate = new AdPosValidate();
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