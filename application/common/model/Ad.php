<?php
// +----------------------------------------------------------------------
// | 广告
// +----------------------------------------------------------------------
// | Author: pc 2020-02-06
// +----------------------------------------------------------------------
namespace app\common\model;
use app\common\validate\Ad as AdValidate;
class Ad extends Base
{
    protected $name = 'ad';
    protected $resultSetType = 'collection';

    protected $autoWriteTimestamp = true;

    protected $type = [
        'start_time'  =>  'timestamp',
        'end_time'  =>  'timestamp',
    ];

    //获取数量
    public function fetchCount(array $condition)
    {
        $count = $this->where($condition)->count();

        return $count;
    }

    //获取列表
    public function fetchList(array $condition, $offset, $limit = 10)
    {
        $list = $this->where($condition)->order('id desc')->limit("$offset,$limit")->select();
        if (empty($list)) {
            return array();
        }

        return $list->toArray();
    }

    //获取详情
    public function getDetail(array $condition)
    {
        $list = $this->where($condition)->find();
        if (empty($list)) {
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
        $validate = new AdValidate();
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

    /**
     * @param null $pos_id
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * [获取列表]
     */
    public function getAdListByPosid($pos_id=null){
        $pos_id = intval($pos_id);
        if($pos_id <= 0){
            return array();
        }
        $time = time();
        $where = array();
        $where['pos_id'] = $pos_id;
        $where['is_show'] = 1;
        $adList = $this->where($where)->whereTime('start_time', '<=', $time)->whereTime('end_time', '>=', $time)->order('orderby desc')->select();
        return empty($adList)?array():$adList->toArray();
    }
}