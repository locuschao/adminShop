<?php

namespace Obs\S3\Signature;

use Obs\Common\Model;

interface SignatureInterface
{
	function doAuth(array &$requestConfig, array &$params, Model $model);
}