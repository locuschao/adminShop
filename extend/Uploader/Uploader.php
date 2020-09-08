<?php
/**
 * Created by PhpStorm.
 * User: cqingt
 * Date: 2017/12/8
 * Time: 19:10
 */
namespace Uploader;

use think\Config;

class Uploader {
    // 允许资源
    protected $host ;
    protected $allowExt = ['jpg','gif','png','bmp','pjpeg','jpeg'];

    protected $allowType = ['image/jpg','image/gif','image/png','image/bmp','image/pjpeg','image/jpeg'];

    // 文件大小上限 单位：byte
    protected $size = 5242880;

    public function __construct()
    {
        $this->host = Config::get('image_url')?Config::get('image_url'):$this->host;
    }

    /**
     * 文件流上传
     * @param $fileStream
     * @param string $dirName
     * @param string $fileName
     * @return array
     */
    public function uploadStream($fileStream, $dirName = '', $fileName = '') {
        preg_match('/^(data:\s*image\/(\w+);base64,)/i', $fileStream, $result);

        if (empty($result)) {
            return ['error_no' => -1,'error_msg' => '文件不存在'];
        }

        $fileStream = str_replace($result[1], '', $fileStream);
        $stream = base64_decode($fileStream);
        $file['size'] = strlen($stream);
        $file['ext']  = $result[2];

        if ($file['size'] > $this->size) { // 判断文件大小
            return ['error_no' => -2,'error_msg' => '上传文件太大'];
        }

        if (! in_array($file['ext'], $this->allowExt)) { // 判断图片文件的格式
            return ['error_no' => -3,'error_msg' => '上传文件格式不支持'];
        }

        if (empty($fileName)) {
            $fileName = time() . rand(11111, 99999);
        }

        $fileName .= '.' . $file['ext'];

        if (empty($dirName)) {
            $fullName = "tui/image/{$fileName}";
        } else {
            $fullName = "tui/image/{$dirName}/{$fileName}";
        }

        if (file_exists($fullName)) {
            return ['error_no' => -4,'error_msg' => '同文件名已存在'];
        }

        $imageUrl = getcwd() . DIRECTORY_SEPARATOR . 'background.' . $file['ext'];
        $fp = fopen($imageUrl, 'w');
        fwrite($fp, $stream);
        fclose($fp);

        $config = ('aliyun' == $this->_trigger) ? $this->_oss_config : $this->_obs_config;
        $upload = new File($config);


        return ['error_no' => 0 ,'error_msg' => '', 'data' => $dirName ? "{$dirName}/{$fileName}" : $fileName];
    }

    /**
     * 文件上传
     * @param $file
     * @param string $dirName
     * @param string $fileName
     * @param array $fileSize
     * @return array
     */
    public function uploadFile($file, $dirName = '', $fileName = '', array $fileSize = []) {
        if ( ! is_uploaded_file($file['tmp_name']) ) { // 判断上传文件是否存在
            return ['error_no' => -1, 'error_msg' => '图片文件不存在'];
        }

        if ( ! empty($fileSize) ) { // 判断文件宽高
            list($width, $height)= getimagesize($file['tmp_name']);
            if ( $width != $fileSize[0] || $height != $fileSize[1] ) {
                return ['error_no' => -2, 'error_msg' => "图片大小为$fileSize[0]*$fileSize[1]"];
            }
        }

        if ( $file['size'] > $this->size ) { // 判断文件大小
            return ['error_no' => -3, 'error_msg' => '上传图片文件太大'];
        }

        if ( ! in_array($file['type'], $this->allowType) ) { // 判断图片文件的格式
            return ['error_no' => -4, 'error_msg' => '上传图片文件格式不支持'];
        }

        list($name, $suffix) = explode('.', $file['name']); // 图片类型

        if ( empty($fileName) ) {
            $fileName = md5_file($file['tmp_name']);
        }

        if ( ! in_array($suffix, $this->allowExt) ) { // 判断图片文件的后缀
            return ['error_no' => -4, 'error_msg' => '上传图片文件后缀不支持'];
        }

        if (!is_dir($dirName))
        {
            global_MkDirs($dirName);
        }

        $fileName .= '.' . $suffix;
        $fullName = $dirName."/".$fileName;
        move_uploaded_file($file['tmp_name'],$fullName );

        return ['error_no' => 0 , 'error_msg' => '', 'data' => $this->host.$fileName];
    }

    public function _getRealFileType($content) {
        $bin      = substr($content, 0, 2);
        $strInfo  = unpack('C2chars', $bin);
        $typeCode = intval($strInfo['chars1'].$strInfo['chars2']);
        $fileType = '';

        switch ( $typeCode ) {
            case 7790:
                $fileType = 'exe';
                break;
            case 7784:
                $fileType = 'midi';
                break;
            case 8297:
                $fileType = 'rar';
                break;
            case 255216:
                $fileType = 'jpg';
                break;
            case 7173:
                $fileType = 'gif';
                break;
            case 6677:
                $fileType = 'bmp';
                break;
            case 13780:
                $fileType = 'png';
                break;
        }

        return $fileType;
    }
}