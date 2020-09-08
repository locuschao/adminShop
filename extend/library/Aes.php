<?php
namespace library;
use think\Config;
use think\Loader;

class Aes{

    /**
     * @param $encryptedData string 加密的用户数据
     * @param $iv string 与用户数据一同返回的初始向量
     * @param $session_key string 解密后的原文
     */
    public static function decryptData( $encryptedData,$session_key, $iv,$appid )
    {
        $result = array(
            'code'=>200,
            'msg'=>'请求成功',
            'data'=>array(),
        );
        log_message('授权时状态码iv:'.$iv, 'log',  Config::get('log_dir').'/decryptData/');
        Loader::import('Wx/wxBizDataCrypt', EXTEND_PATH);
        $pc = new \WXBizDataCrypt($appid,$session_key);
        $errCode = $pc->decryptData($encryptedData, $iv, $data );
        log_message('授权时状态码:'.$errCode, 'log',  Config::get('log_dir').'/decryptData/');
        log_message('授权时数据:'.$data, 'log', Config::get('log_dir').'/decryptData/');
        if ($errCode == 0) {
            $result['data']=json_decode($data,true);
            return json_encode($result);
        } else {
            $result['code']=$errCode;
            $result['msg']='解析失败';
            return json_encode($result);
        }
    }


}

