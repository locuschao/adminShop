<?php

namespace Obs\Log;

class S3Config
{
	const LOG_FILE_CONFIG = [
			'FilePath'=>'./logs',
			'FileName'=>'eSDK-OBS-PHP.log',
			'MaxFiles'=>10,
			'Level'=>INFO
	];
}
