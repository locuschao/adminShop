<?php
// +----------------------------------------------------------------------
// | 配置管理
// +----------------------------------------------------------------------
// | Author: Wuyh 2020-02-13
// +----------------------------------------------------------------------
namespace app\admin\controller;

use app\common\model\SysConfig AS SysConfigModel;
use app\common\model\SysConfigGroup;

class SysConfig extends Base
{
    //配置表模型
    protected $sysConfigGroupModel;

    //配置分组模型
    protected $sysConfigModel;

    public function _initialize()
    {
        parent::_initialize(); // TODO: Change the autogenerated stub

        $this->sysConfigGroupModel = new SysConfigGroup();
        $this->sysConfigModel = new SysConfigModel();
    }

    /**
     * 列表
     * @return string
     * @throws \think\Exception
     */
    public function index()
    {
        $groupId = request()->param('group_id', 0);
        $configGroupList = $this->sysConfigGroupModel->where(['status' => 1])->select();

        if ($this->request->isAjax()){
            return $this->sysConfigModel->tableData(input('param.'));
        }

        return $this->view->fetch('', [
            'group_id' => $groupId,
            'config_group_list' => $configGroupList->toArray(),
        ]);
    }

    /**
     * 编辑
     * @return string
     * @throws \think\Exception
     */
    public function edit()
    {
        $id = request()->param('id', 0);

        if (!$id)  $this->error('ID参数错误');
        $info = $this->sysConfigModel->find(['id' => $id]);

        if (empty($info)) $this->error('找不到结果');
        $groupList = SysConfigGroup::where(['pid' => $info['group_id']])->select()->toArray();
        $this->assign('group_id', $info['group_id']);

        if ($this->request->isGet()) return $this->view->fetch('', ['info' => $info, 'groupList' => $groupList]);
        if ($this->request->isPost()) return $this->sysConfigModel->toAdd(input('post.'));
    }

    /**
     * 创建
     * @return string|\think\response\Json
     * @throws \think\Exception
     */
    public function add()
    {
        $groupId = request()->param('group_id', 0);
        if (empty($groupId))  $this->error('请选择配置组');

        $groupList = SysConfigGroup::where(['pid' => $groupId])->select()->toArray();
        $this->assign('group_id', $groupId);

        if ($this->request->isGet()) return $this->view->fetch('edit', ['info' => [], 'groupList' => $groupList]);
        if ($this->request->isPost()) return $this->sysConfigModel->toAdd(input('post.'));
    }

    /**
     * 删除
     * @param $id
     * @return \think\response\Json
     */
    public function del($id)
    {
        if(false === SysConfigModel::destroy($id)) json(['code' => 0, 'data' => '', 'msg' => '失败']);;
        return json(['code' => 1, 'data' => '', 'msg' => '成功']);
    }

    /**
     * 配置
     * @return mixed|\think\response\Json
     */
    public function group()
    {
        $groupId = request()->param('group_id', 1);

        $groupList = $this->sysConfigGroupModel->getTree();
        $children = $this->sysConfigGroupModel->with('configs')->where(['pid' => $groupId])->select();

        if (!$children->isEmpty()){
            foreach ($children as &$val) {
                foreach ($val['configs'] as &$config) {
                    if ($config['type'] == 'select') {
                        $config['format_name'] = "{$config['name']}|1|{$config['title']}|name|id";
                    }
                }
            }
        }

        if ($this->request->isGet()) return $this->fetch('', [
            'config_group_list' => $groupList,
            'group_id' => $groupId,
            'children' => $children,
            ]);

        if ($this->request->isPost()) {
            $params = input("post.");
            $this->sysConfigModel->setValue($params);
            return json(['code' => 1, 'data' => '', 'msg' => '成功']);
        }
    }
}