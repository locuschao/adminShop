<?php
namespace library;

use app\common\model\AdminUser;
use think\Cookie;
use think\Request;
use think\Session;

class Auth {
    
    /**
     * @var object 对象实例
     */
    protected static $instance;

    
    protected static $requestUri = '';

    protected static $key = 'admin';

    final protected function __construct() {}
    
    final protected function __clone() {}
    
    public function __get($name) {
        return Session::get('admin.' . $name);
    }

    /**
     * 初始化
     * @access public
     * @param array $options 参数
     * @return Auth
     */
    public static function instance($options = []) {
        if ( is_null(self::$instance) ) {
            self::$instance = new static($options);
        }

        return self::$instance;
    }

    /**
     * 登录
     */
    public static function login($username, $password, $keeptime = 0) {
        $admin = AdminUser::get(['account' => $username, 'isable' => 1]);

        if ( ! $admin ) {
            return array('code'=>0);
        }

        if ( ! password_verify($password, $admin->password) ) {
            return array('code'=>0);
        }

        Session::set("admin", $admin->toArray());
        
        self::keeplogin($keeptime);
        
        return array('code'=>200);
    }

    /**
     * 手机号登录验证
     */
    public static function checkLogin($username,$keeptime = 0){
        $admin = AdminUser::get(['account' => $username, 'isable' => 1]);
        if ( ! $admin ) {
            return false;
        }
        Session::set("admin", $admin->toArray());
        self::keeplogin($keeptime);
        return true;
    }
    
    /**
     * 注销登录
     */
    public static function logout() {
        Session::delete("admin");
        return true;
    }    
    
    /**
     * 自动登录
     * @return boolean
     */
    public static function autologin() {
        $keeplogin = Cookie::get('keeplogin');
        if ( ! $keeplogin ) {
            return false;
        }
        
        list($id, $keeptime, $expiretime, $key) = explode('|', $keeplogin);
        
        if ( $id && $keeptime && $expiretime && $key && $expiretime > time() ) {
            $admin = AdminUser::get($id);
            if ( ! $admin ) {
                return false;
            }
            
            // 判断加密串是否被篡改
            if ( $key != md5(md5($id) . md5($keeptime) . md5($expiretime)) ) {
                return false;
            }
            
            Session::set("admin", $admin->toArray());
            
            // 刷新自动登录的时效
            self::keeplogin($keeptime);
            
            return true;
        } else {
            return false;
        }
    }
    
    /**
     * 刷新保持登录的Cookie
     * @param int $keeptime
     * @return boolean
     */
    protected static function keeplogin($keeptime = 0) {
        if ( $keeptime ) {
            $expiretime = time() + $keeptime;
            $key        = md5(md5(self::$key) . md5($keeptime) . md5($expiretime));
            $data       = [self::$key, $keeptime, $expiretime, $key];
            
            Cookie::set('keeplogin', implode('|', $data));
            
            return true;
        }
        
        return false;
    }
    
    /**
     * 检测是否登录
     *
     * @return boolean
     */
    public static function isLogin() {
        return Session::has('admin');
    }

    public static function getAdmin(){
        return Session::get('admin');
    }

    /**
     * 检测当前控制器和方法是否匹配传递的数组
     *
     * @param array $arr 需要验证权限的数组
     */
    public static function match($arr = []) {
        $array   = is_array($arr) ? $arr : explode(',', $arr);
        if ( ! $array ) {
            return FALSE;
        }

        // 是否存在
        if ( in_array(strtolower(self::$requestUri), $array) || in_array('*', $array) ) {
            return TRUE;
        }

        // 没找到匹配
        return FALSE;
    }
    
    /**
     * 获取当前请求的URI
     * @return string
     */
    public static function getRequestUri() {
        return self::$requestUri;
    }

    /**
     * 设置当前请求的URI
     * @param string $uri
     */
    public static function setRequestUri($uri) {
        self::$requestUri = $uri;
    }
    
    /**
     * 获得当前登录账号资料
     * @return array
     */
    public static function getAdminInfo() {
        return Session::get('admin');
    }
}