<?php
// +----------------------------------------------------------------------
// | 定时任务表
// +----------------------------------------------------------------------
// | Copyright (c) 2019-2020 rights reserved.
// +----------------------------------------------------------------------
// | Author: wuyh
// +----------------------------------------------------------------------
// | Date: 2020/3/28 16:02
// +----------------------------------------------------------------------

namespace app\common\model;

use think\Model;


class SwooleCrontab extends Model
{
    protected $name = 'swoole_crontab';
    protected $autoWriteTimestamp = true;
    protected $createTime = 'create_at';
    protected $updateTime = 'update_at';


    /**
     * 返回table所需要的数据格式
     * @param $params
     * @return mixed
     * @Author: wuyh
     * @Date: 2020/3/28 16:03
     */
    public function tableData($params)
    {
        if (isset($params['limit'])) {
            $limit = $params['limit'];
        } else {
            $limit = config('cfg.SYS_PAGE');
        }

        $condition = $this->_tableCondition($params);

        $list = $this
            ->where($condition['where'])
            ->order($condition['order'])
            ->paginate($limit);

        $data = $this->_tableFormat($list->getCollection());

        $re['code'] = 0;
        $re['msg'] = '';
        $re['count'] = $list->total();
        $re['data'] = $data;

        return $re;
    }

    /**
     * 查询条件
     * @param $params
     * @return mixed
     * @Author: wuyh
     * @Date: 2020/3/28 16:04
     */
    private function _tableCondition($params)
    {
        $map = [];

        if (isset($params['name']) && $params['name'] != "") $map['name'] = ['like', $params['name'] . '%'];
        if (isset($params['status']) && $params['status'] != "") $map['status'] = $params['status'];

        $result['where'] = $map;
        $result['field'] = "*";
        $result['order'] = 'id desc';
        return $result;
    }

    /**
     * 格式化表格数据
     * @param $list
     * @return mixed
     * @Author: wuyh
     * @Date: 2020/3/28 16:06
     */
    private function _tableFormat($list)
    {
        return $list;
    }

    /**
     * 新增/修改
     * @param $params
     * @return array
     * @Author: wuyh
     * @Date: 2020/3/28 16:33
     */
    public function toAdd($params)
    {
        $data = [
            'rule' => trim($params['rule']),
            'status' => intval($params['status']),
            'name' => trim($params['name']),
            'execute_class' => trim($params['execute_class']),
            'remark' => trim($params['remark']),
        ];
        if (empty($params['id'])) {
            $ret = $this->allowField(true)->save($data);
        } else {
            $ret = $this->allowField(true)->where(['id' => $params['id']])->update($data);
        }

        if (!$ret) return ['code' => 0, 'msg' => $this->getError()];
        return ['code' => 1, 'msg' => 'SUCCESS'];
    }

}