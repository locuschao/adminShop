<?php
/**
 * Des加密类
 * @package crypt
 */

namespace library;

class DesUtil{
    private static $BYTES_KEY = array(21,21,21,55,76,60,33,24); //key
    private static $BYTES_IV  = array(99,22,22,33,46,33,22,46); //vetor
    private static $crypt;

    /**
     * des加密
     * @static
     * @param string $str
     * @return string
     * @assert ('abc') == '573B52D789A0D913'
     */
    static function encrypt($str, $key='', $iv=''){
        self::getInstance($key, $iv);

        $mstr = self::$crypt->encrypt($str);
        return $mstr;
    }

    /**
     * des解密
     * @static
     * @param string $mstr
     * @return string
     * @assert ('573B52D789A0D913') == 'abc'
     */
    static function decrypt($mstr, $key='', $iv=''){
        self::getInstance($key, $iv);
        $str = self::$crypt->decrypt($mstr);
        return $str;
    }

    /**
     * @static
     * @access private
     * @param type $key
     * @param type $iv
     */
    static private function getInstance($key, $iv){
        if(!empty ($key)){
            self::$BYTES_KEY = $key;
        }
        if(!empty ($iv)){
            self::$BYTES_IV = $iv;
        }
        if(!self::$crypt){
            $key =  ByteUtil::bytes2str(self::$BYTES_KEY);
            $iv  =  ByteUtil::bytes2str(self::$BYTES_IV);
            self::$crypt = new Des($key, $iv);
        }
    }
}


