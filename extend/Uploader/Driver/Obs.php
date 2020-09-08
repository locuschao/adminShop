<?php

$mapping = [
	'Obs\Common\Constants' => __DIR__.'/Obs/Common/Constants.php',
	'Obs\Common\Model' => __DIR__.'/Obs/Common/Model.php',
	'Obs\Common\ObsException' => __DIR__.'/Obs/Common/ObsException.php',
	'Obs\Common\SchemaFormatter' => __DIR__.'/Obs/Common/SchemaFormatter.php',
	'Obs\Common\SdkCurlFactory' => __DIR__.'/Obs/Common/SdkCurlFactory.php',
	'Obs\Common\SdkStreamHandler' => __DIR__.'/Obs/Common/SdkStreamHandler.php',
	'Obs\Common\ToArrayInterface' => __DIR__.'/Obs/Common/ToArrayInterface.php',
	'Obs\Log\S3Config' => __DIR__.'/Obs/Log/S3Config.php',
	'Obs\Log\S3Log' => __DIR__.'/Obs/Log/S3Log.php',
	'Obs\S3\GetResponseTrait' => __DIR__.'/Obs/S3/GetResponseTrait.php',
	'Obs\S3\ObsClient' => __DIR__.'/Obs/S3/ObsClient.php',
	'Obs\S3\Resource\RequestResource' => __DIR__.'/Obs/S3/Resource/RequestResource.php',
	'Obs\S3\SendRequestTrait' => __DIR__.'/Obs/S3/SendRequestTrait.php',
	'Obs\S3\Signature\AbstractSignature' => __DIR__.'/Obs/S3/Signature/AbstractSignature.php',
	'Obs\S3\Signature\SignatureInterface' => __DIR__.'/Obs/S3/Signature/SignatureInterface.php',
	'Obs\S3\Signature\V2Signature' => __DIR__.'/Obs/S3/Signature/V2Signature.php',
	'Obs\S3\Signature\V4Signature' => __DIR__.'/Obs/S3/Signature/V4Signature.php',
];

spl_autoload_register(function ($class) use ($mapping) {
    if (isset($mapping[$class])) {
        require $mapping[$class];
    }
}, true);
