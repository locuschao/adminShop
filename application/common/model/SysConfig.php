<?php
// +----------------------------------------------------------------------
// | 系统配置表
// +----------------------------------------------------------------------
// | Author: Wuyh 2020-02-13
// +----------------------------------------------------------------------
namespace app\common\model;

use think\Model;
use library\CacheKey;

class SysConfig extends Model
{
    protected $name = 'sys_config';
    protected $resultSetType = 'collection';
    protected $autoWriteTimestamp = true;
    protected $createTime = 'create_at';
    protected $updateTime = 'update_at';

    const ENABLE = 1; //可用
    const DISABLE = 0; //不可用

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
        $data = $this->_tableFormat($list->getCollection());

        $re['code'] = 0;
        $re['msg'] = '';
        $re['count'] = $list->total();
        $re['data'] = $data;

        return $re;
    }

    /**
     * 格式化表格数据
     * @param $list
     * @return mixed
     */
    private function _tableFormat($list)
    {
        foreach($list as $k => $v) {
            if(isset($v['sub_id']) && $v['sub_id']) $list[$k]['sub_name'] = $v->subGroup->title;
        }
        return $list;
    }

    /**
     * 添加/编辑
     * @param $param
     * @return array
     */
    public function toAdd($param)
    {
        $scene = 'SysConfig.save';
        $id = isset($param['id']) ? intval($param['id']) : 0;
        if ($id){
            $ret = $this->validate($scene)->save($param, $id);
        }else{
            $ret = $this->validate($scene)->save($param);
        }

        if ($ret === false) return ['code' => 0, 'msg' => $this->getError()];

        cache(CacheKey::SITE_SYS_CONFIG,null);
        return ['code' => 1, 'msg' => '成功'] ;
    }

    /**
     * 表格查询条件
     * @param $params
     * @return mixed
     */
    private function _tableCondition($params)
    {
        $map = [];
        if (isset($params['group_id']) && $params['group_id'] != "") $map['group_id'] = intval($params['group_id']);

        $result['where'] = $map;
        $result['field'] = "*";
        $result['order'] = 'id desc';
        return $result;
    }

    /**
     * 保存配置值
     * @param array $params
     * @return bool
     * @throws \Exception
     */
    public function setValue($params = [])
    {
        if (empty($params)) return false;
        foreach ($params as $key => $val){
            //备注: wuyh 需调
            $this->where(['name' => $key])->update(['value' => $val]);
        }
        cache(CacheKey::SITE_SYS_CONFIG,null);
        return true;
    }

    /**
     * 关联具体分组
     * @return \think\model\relation\HasOne
     */
    public function subGroup()
    {
        return $this->hasOne('SysConfigGroup', 'id', 'sub_id');
    }

    /**
     * 获取全部配置信息
     * @param bool $cache
     * @return array
     */
    public function getConfigInfo($cache = false)
    {
        $cache = $cache ? CacheKey::SITE_SYS_CONFIG : false;
        $list = $this->cache($cache)
            ->where(['status' => static::ENABLE,'sub_id' => ['gt',0]])
            ->column('value', 'name');
        return $list;
    }
}