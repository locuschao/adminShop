<?php
namespace app\admin\controller;
use think\Config;
use Uploader\Uploader;
/**
 * Class Upload
 * @package app\admin\controller
 * [上传]
 */
class Upload extends Base{
     public function image(){
         $result = array(
             'code'=> 0,
             'msg'=> "",
             'url'=> "",
         );
         $file = $this->request->file('file');
         if (empty($file))
         {
             $result['msg'] = "文件格式不正确";
             echo  json_encode($result);exit();
         }

         if (is_file($file->getInfo('filename')['tmp_name'])) {
             $fileinfo = $file->getInfo('filename');
             if((substr_count($fileinfo['name'], '.') >= 2)) {
                 $result['msg'] = "文件格式不正确";
                 echo  json_encode($result);exit();
             }
             $res = (new Uploader())->uploadFile($fileinfo,Config::get('image_dir'));
             if ( $res['error_no'] == 0 ) {
                 $result['code'] = "1";
                 $result['msg'] = "上传成功";
                 $result['url'] = $res['data'];
                 echo  json_encode($result);exit();
             }else{
                 $result['msg'] = $res['error_msg'];
                 echo  json_encode($result);exit();
             }
         }
         $result['msg'] = "上传失败";
         echo  json_encode($result);exit();
     }

     //上传规格图片
     public function goodsSpec(){
         $result = array(
             'code'=> 0,
             'msg'=> "",
             'url'=> "",
         );
         $file = $this->request->file('file');
         $item_id = $this->request->get('item_id');
         if (empty($file))
         {
             $result['msg'] = "文件格式不正确";
             echo  json_encode($result);exit();
         }

         if (is_file($file->getInfo('filename')['tmp_name'])) {
             $fileinfo = $file->getInfo('filename');
             if((substr_count($fileinfo['name'], '.') >= 2)) {
                 $result['msg'] = "文件格式不正确";
                 echo  json_encode($result);exit();
             }
             $res = (new Uploader())->uploadFile($fileinfo,Config::get('image_dir'));
             if ( $res['error_no'] == 0 ) {
                 $result['code'] = "1";
                 $result['msg'] = "上传成功";
                 $result['url'] = $res['data'];
                 $result['id'] = $item_id;
                 echo  json_encode($result);exit();
             }else{
                 $result['msg'] = $res['error_msg'];
                 echo  json_encode($result);exit();
             }
         }
         $result['msg'] = "上传失败";
         echo  json_encode($result);exit();
     }

     public function layImage(){
         $result = array(
             'code'=> 0,
             'msg'=> "",
             'data'=> "",
         );
         $file = $this->request->file('file');
         if (empty($file))
         {
             $result['msg'] = "文件格式不正确";
             echo  json_encode($result);exit();
         }

         if (is_file($file->getInfo('filename')['tmp_name'])) {
             $fileinfo = $file->getInfo('filename');
             if((substr_count($fileinfo['name'], '.') >= 2)) {
                 $result['msg'] = "文件格式不正确";
                 echo  json_encode($result);exit();
             }
             $res = (new Uploader())->uploadFile($fileinfo,Config::get('image_dir'));
             if ( $res['error_no'] == 0 ) {
                 $result['code'] = 0;
                 $result['msg'] = "上传成功";
                 $result['data'] = array('src'=>$res['data']);
                 echo  json_encode($result);exit();
             }else{
                 $result['msg'] = $res['error_msg'];
                 echo  json_encode($result);exit();
             }
         }
         $result['msg'] = "上传失败";
         echo  json_encode($result);exit();
     }
}
