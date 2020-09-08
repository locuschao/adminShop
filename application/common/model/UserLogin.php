<?php
//用户日志登录表
namespace app\common\model;
use think\Exception;

class UserLogin extends Base{
    // 表名
    protected $name = 'user_login';

    public static function insertLog($user_id){
        $data = array(
            'user_id'=>$user_id,
            'login_ip'=>getClientIp(),
            'log_ip_location'=>$user_id,
            'login_time'=>time(),
        );
        try{
            self::insert($data);
        }catch ( Exception $e){

        }
    }
}