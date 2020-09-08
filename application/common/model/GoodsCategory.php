<?php
namespace app\common\model;
class GoodsCategory extends Base{
    // 表名
    protected $name = 'goods_category';

    const ONE_LEVEl = 1;//一级
    const TWO_LEVEl = 2;//二级

    //获取数量
    public function fetchCount(array $condition) {
        $count = $this->where($condition)->count();

        return $count;
    }

    //获取多少条数
    public function fetchList(array $condition, $offset,$limit = 10) {
        $list = $this->where($condition)->order('id desc')->limit("$offset,$limit")->select();
        if(empty($list)){
            return array();
        }

        return $list->toArray();
    }

    //获取所有
    public function getTreeMenu( $where = array()){
        $menu = $this->order('weight desc,id asc')->where($where)->select();
        if(empty($menu)){
            return array();
        }
        $menu = $menu->toArray();
        return $menu;
    }

    //获取单个详情
    public function getDetail(array $condition){
        $list = $this->where($condition)->find();
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
        $validate = new \app\common\validate\GoodsCategory();
        $result = $validate->scene('save')->check($params);

        if (!$result) return ['code' => 0, 'msg' => $validate->getError()];

        $ret = (isset($params['id']) && !empty($params['id'])) ? $this->allowField(true)->save($params, $params['id']) : $this->allowField(true)->save($params);
        if (!$ret) return ['code' => 0, 'msg' => $this->getError()];

        return ['code' => 1, 'msg' => 'SUCCESS'];
    }

}
