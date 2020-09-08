<?php
namespace app\admin\controller;
use app\common\model\AdminMenu;
use app\common\model\AdminRole;
use think\Controller;
use library\Auth;

class Base extends Controller
{
    //模型
    protected $model;

    /**
     * 无需登录的方法
     * @var array
     */
    protected $noNeedLogin = ['index/index','index/login','index/logout','index/verify'];

    protected $noNeedRight = [];

    protected $admin = [];

    public function _initialize()
    {
        parent::_initialize(); // TODO: Change the autogenerated stub
        $this->commonData();
        $this->init();
    }



    public function init(){
        $path = $this->request->path();

        $auth = Auth::instance();
        // 设置当前请求的URI
        $auth::setRequestUri($path);
        if ( ! $auth::match($this->noNeedLogin) ) {
            if ( ! $auth->isLogin() ) {
                return $this->redirect('index/login');
            }
            $admin = Auth::getAdmin();

            if($admin['role']!=1){
                // 判断是否需要验证权限
                if (!$auth::match($this->noNeedRight))
                {
                    // 判断控制器和方法判断是否有对应权限
                    if (!$this->checkDataPriv($path,$admin['role']))
                    {
                        $this->error("您没有权限操作,被强制退出",'index/logout',[],1);
                    }
                }
            }

            $this->admin = $admin;

        }


        //菜单
        $nav = array();
        $admin = array();
        if ($auth::isLogin()) {
            $admin = $auth::getAdminInfo();
            //超级管理员
            if($admin['level']==1){
                //查询菜单
                $AdminMenu = new AdminMenu();
                $menu = $AdminMenu -> getTreeMenu(array('type'=>1,"isshow"=>1));
                $nav = $AdminMenu -> getTree($menu,0);
            }

            //普通管理员
            if($admin['level']==2){
                //通过角色查询菜单
                $roleid = $admin['role'];
                $adminRole = new AdminRole();
                $roleInfo = $adminRole->getRoleInfoById($roleid);

                $rule = unserialize($roleInfo['rule']);
                $AdminMenu = new AdminMenu();
                $menu = $AdminMenu -> getTreeMenu(array('id'=>array('in',$rule),"isshow"=>1));
                $nav = $AdminMenu -> getTree($menu,0);
            }
        }
        $this->assign('admin', $admin);
        $this->assign('nav',$nav);
    }

    //统用变量
    private function commonData(){
        $ico =  'favicon.ico';
        $this->assign('ico',$ico);
        // 根据域名配置logo
        $logo =  'logo_login.png';
        $this->assign('logo',$logo);

        $admin_name = "后台功能展示";
        $this->assign('admin_name',$admin_name);

        $copyright = "她她传媒有限公司版权所有";
        $this->assign('copyright',$copyright);
    }

    /**
     * 检查管理员的数据操作权限
     * @param string $action_key
     * @param array $menu
     * @return boolean
     */
    private function checkDataPriv($action_key,$role_id){
        if($this->request->isAjax()){
         return true;
        }
        $adminRole = new AdminRole();
        $roleInfo = $adminRole->getRoleInfoById($role_id);
        $rule = unserialize($roleInfo['rule']);
        $AdminMenu = new AdminMenu();
        $menu = $AdminMenu -> getTreeMenu(array('id'=>array('in',$rule),"isshow"=>1));
        $action_key_data = array_column($menu,'action');
        foreach ($action_key_data as $k=>&$v){
            $v=strtolower($v);
        }
        if(in_array(strtolower($action_key),$action_key_data)){

            return true;
        }

        return false;
    }




    /**
     * 检查参数
     * @param string $field
     * @return array
     * @Author: wuyh
     * @Date: 2020/3/26 19:18
     */
    public function checkParams($field=''){
        $need   = [];   //必填字段
        $this->post = input('post.');

        if($field) {
            $need = explode(',', $field);
            foreach ($need as $val) {
                if (!isset($this->post[$val]) || is_null($this->post[$val]) || $this->post[$val] === '') {
                    return ['code' => 0, 'msg' => '参数' . $val . '不能为空！'];
                }
            }
        }

        return ['code' => 1, 'msg' => '成功'];
    }
}