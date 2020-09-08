<?php
namespace library;
class TokenUtil{
    private static $key = "chaoji@qq.com";

    public static function createToken( $unique,$time){
        return DesUtil::encrypt(md5(self::$key.$time.$unique).$time);
    }

    public static function checkToken($token, $unique, &$error = null){
        $str = DesUtil::decrypt($token);
        if(empty($str) || strlen($str) < 32){
            $error = 'param error';
            return false;
        }

        $md5 = substr($str, 0, 32);
        $time = substr($str, 32);
        if(abs(time() - floatval($time)) > 300){
            echo abs(time() - floatval($time));die;
            $error = 'token timeout';
            return false;
        }

        if($md5 != md5(self::$key.$time.$unique)){
            $error = 'token invalid';
            return false;
        }

        return true;
    }

}