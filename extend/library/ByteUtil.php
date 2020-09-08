<?php
namespace library;
/**
 * Byte工具类
 * @package crypt
 */
class ByteUtil {

    /**
     * str2bytes
     *
     * @static
     *
     * @access public
     * @param string $str
     * @return array
     */
    static function str2bytes($str){
        // $str = iconv('utf-8', 'UTF-16BE', $str);
        $len = strlen($str);
        $bytes = array();
        for($i = 0; $i < $len; $i ++){
            $bytes[] = ord($str[$i]);
        }
        return $bytes;
    }

    /**
     * bytes2str
     *
     * @static
     *
     * @access public
     * @param array $bytes
     * @return string
     */
    static function bytes2str($bytes){
        $str = '';
        foreach($bytes as $ch){
            $str .= chr($ch);
        }
        return $str;
    }

    static function int2bytes2($var, $isHighFirst = false){
        $result = array();
        if($isHighFirst){
            $result[0] = ($var >> 24 & 0xFF);
            $result[1] = ($var >> 16 & 0xFF);
        }else{
            $result[0] = ($var & 0xFF);
            $result[1] = ($var >> 8 & 0xFF);
        }
        return $result;
    }

    static function int2bytes4($var, $isHighFirst = false){
        $result = array();
        if($isHighFirst){
            $result[0] = ($var >> 24 & 0xFF);
            $result[1] = ($var >> 16 & 0xFF);
            $result[2] = ($var >> 8 & 0xFF);
            $result[3] = ($var & 0xFF);
        }else{
            $result[0] = ($var & 0xFF);
            $result[1] = ($var >> 8 & 0xFF);
            $result[2] = ($var >> 16 & 0xFF);
            $result[3] = ($var >> 24 & 0xFF);
        }
        return $result;
    }

    static function bytes2int(array $bytes, $offset = 0, $len = 0){
        if($len > 0){
            $bytes = array_slice($bytes, $offset, $len);
        }

        $length = count($bytes);
        $intValue = 0;
        for($i = $length - 1; $i >= 0; $i --){
            $offset = $i * 8; // 24, 16, 8
            $intValue |= ($bytes[$i] & 0xFF) << $offset;
        }
        return $intValue;
    }

    static function int2byte($int){
        if($int > 0)
            return $int;
        return $int & 0xFF;
    }

    static function bytes2Hash(array $bytes){
        $len = count($bytes);
        $s = '';
        for($i = 0; $i < $len; $i ++){
            if($bytes[$i] < 0){
                $bytes[$i] = 256 + $bytes[$i];
            }
            $hex = dechex($bytes[$i]);
            if($bytes[$i] < 16){
                $hex = '0' . $hex;
            }
            $s .= strval($hex);
        }

        return strtoupper($s);
    }

    static function hash2Bytes($hash){
        $arr = array();
        $count = strlen($hash) / 2;

        for($i = 0; $i < $count; $i ++){
            $arr[$i] = hexdec($hash[$i * 2] . $hash[$i * 2 + 1]);
        }
        return $arr;
    }
}
