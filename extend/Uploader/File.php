<?php
/**
 * oos与obs上传
 */
namespace Uploader;

class File {
    private static $_uploader = [];
    private $_error = '';
    private $_config = [];

    public function __construct($config) {
        $this->_config = $config;
    }

    public function saveUploadFileToOss($uploadFile, $saveFilePath) {
        $saveResult = false;

        if ( empty(self::$_uploader['oss']) ) {
            require __DIR__ . DIRECTORY_SEPARATOR . 'Driver' . DIRECTORY_SEPARATOR . 'Oss.php';

            self::$_uploader['oss'] = true;
        }

        try {
            $accessKeyId     = $this->_config['OSS_ACCESS_ID'];
            $accessKeySecret = $this->_config['OSS_ACCESS_KEY'];
            $endpoint        = $this->_config['OSS_ENDPOINT'];
            $bucket          = $this->_config['OSS_BUCKET'];
            $ossClient       = new \OSS\OssClient($accessKeyId, $accessKeySecret, $endpoint);
            $ossClient->setTimeout(3600);
            $ossClient->setConnectTimeout(10);
            $ossClient->uploadFile($bucket, $saveFilePath, $uploadFile);
            $saveResult = true;
        } catch (\OSS\Core\OssException $e) {
            $this->setError('文件上传到OSS时出错');
        }

        return $saveResult;
    }

    public function saveUploadFileToObs($uploadFile, $saveFilePath) {
        $saveResult = false;

        if ( empty(self::$_uploader['obs']) ) {
            require __DIR__ . DIRECTORY_SEPARATOR . 'Driver' . DIRECTORY_SEPARATOR . 'Obs.php';

            self::$_uploader['obs'] = true;
        }

        try {
            $accessKeyId     = $this->_config['OBS_ACCESS_ID'];
            $accessKeySecret = $this->_config['OBS_ACCESS_KEY'];
            $endpoint        = $this->_config['OBS_ENDPOINT'];
            $bucket          = $this->_config['OBS_BUCKET'];
            $obsClient = \Obs\S3\ObsClient::factory ( [
                'key'       => $accessKeyId,
                'secret'    => $accessKeySecret,
                'endpoint'  => $endpoint
            ] );

            $res = $obsClient->putObject(array(
                'Bucket'    => $bucket,
                'Key'       => $saveFilePath,
                'SourceFile'=> $uploadFile,
            ));

            if ( $res['HttpStatusCode'] == '200' ) {
                $saveResult = true;
            }
        } catch (\Obs\Common\ObsException $e) {
            $this->setError('文件上传到OBS时出错');
        }

        return $saveResult;
    }

    public function setError($error) {
        $this->_error = $error;
    }

    public function getError() {
        return $this->_error;
    }
} // end class