<?php
// +----------------------------------------------------------------------
// | 系统配置分组表
// +----------------------------------------------------------------------
// | Author: Wuyh 2020-02-13
// +----------------------------------------------------------------------
namespace app\common\model;

use think\Model;

class SysConfigGroup extends Model
{
    protected $name = 'sys_config_group';
    protected $resultSetType = 'collection';
    protected $autoWriteTimestamp = true;
    protected $createTime = 'create_at';
    protected $updateTime = 'update_at';

    /**
     * 返回table所需要的数据格式
     * @param $params
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function tableData($params)
    {
        if (isset($params['limit'])) {
            $limit = $params['limit'];
        } else {
            $limit = config('paginate.list_rows');
        }

        $condition = $this->_tableCondition($params);
        $list = $this->where($condition['where'])->paginate($limit);
        $data = $list->getCollection();

        $re['code'] = 0;
        $re['msg'] = '';
        $re['count'] = $list->total();
        $re['data'] = $data;

        return $re;
    }

    /**
     * 添加/编辑
     * @param $param
     * @return array
     * @throws \think\exception\DbException
     */
    public function toAdd($param)
    {
        $scene = 'SysConfigGroup.save';
        $id = isset($param['id']) ? intval($param['id']) : 0;
        if (isset($param['pid']) && $param['pid'] > 0){
            $pInfo = $this->where(['id' => intval($param['pid'])])->find();
            $param['level'] = $pInfo['level'] + 1;
        }

        if ($id){
            $ret = $this->validate($scene)->save($param, $id);
        }else{
            $ret = $this->validate($scene)->save($param);
        }

        return ($ret === false) ?  ['code' => 0, 'msg' => $this->getError()] : ['code' => 1, 'msg' => '成功'];
    }

    /**
     * 获取树形结构的数据
     * @param $params
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getTree($params = [])
    {
        $condition = $this->_tableCondition($params);
        $data = $this->_tableFormat($this->where($condition['where'])->order($condition['order'])->select());
        return getChild($data);
    }

    /**
     * 格式化表格数据
     * @param $list
     * @return mixed
     */
    private function _tableFormat($list)
    {
        return $list;
    }

    /**
     * 表格查询条件
     * @param $params
     * @return mixed
     */
    private function _tableCondition($params)
    {
        $map = [];
        if (isset($params['level']) && $params['level'])  $map['level'] = $params['level'];

        $result['where'] = $map;
        $result['field'] = "*";
        $result['order'] = 'sort asc';
        return $result;
    }

    /**
     * 关联配置
     * @return \think\model\relation\HasMany
     */
    public function configs()
    {
        return $this->hasMany('SysConfig', 'sub_id', 'id');
    }
}