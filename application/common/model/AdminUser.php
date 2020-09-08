<?php
namespace app\common\model;

class AdminUser extends Base{
    // 表名
    protected $name = 'admin_user';

      // 结果集
      protected $resultSetType = 'collection';

      //超级管理员 id
      //@author wuyh
      const SUPER_ID = 1;

    public function hasUserName($username) {
        $admin = $this->get(['name' => $username]);

        return $admin ? (bool)$admin->getAttr('id') : false;
    }

    public function getAdminUserByUsername($username){
        $username = trim($username);
        if(empty($username)){
            return array();
        }
        $user = $this->where(array('account'=>$username))->find();
        if(empty($user)){
            return array();
        }
        $user = $user->toArray();
        return $user;
    }

    public function fetchCount(array $condition) {
        $count = $this->where($condition)->count();

        return $count;
    }

    public function fetchList(array $condition, $offset=0,$limit = 10) {
        $list = $this->alias("user")->field("user.*,role.name as role_name")->where($condition)->join('cc_admin_role role','role.id = user.role','left')->order('user.id desc')->limit("$offset,$limit")->select();
        if(empty($list)){
            return array();
        }

        return $list->toArray();
    }

    public function getUserInfoById($id){
        $id = intval($id);
        if($id<=0){
            return array();
        }
        $user = $this->where(array('id'=>$id))->find();
        if(empty($user)){
            return array();
        }
        $user = $user->toArray();
        return $user;
    }

    /**
     * 获取全部管理员
     * @return array
     * @author wuyh
     */
    public function getAll()
    {
        $data = $this->where('id', 'neq', self::SUPER_ID)->order('id asc')->select();
        if (!$data->isEmpty()) return $data->toArray();
        return [];
    }

    /**
     * 关联角色
     * @return \think\model\relation\HasOne
     * @Author: wuyh
     * @Date: 2020/3/10 18:37
     */
    public function adminRole()
    {
        return $this->hasOne('AdminRole', 'id', 'role');
    }

}
