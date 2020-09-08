<?php
namespace library;
class Response{
    public static function Json($code,$msg,$count,$data){
        echo  json_encode(['code'=>$code,'msg'=>$msg,'count'=>$count,'data'=>$data]);exit();
    }
}