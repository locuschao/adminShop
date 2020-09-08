<?php

function AliOssSdkLoader($class) {
	$path = str_replace('\\', DIRECTORY_SEPARATOR, $class);
	$file = __DIR__ . DIRECTORY_SEPARATOR . $path . '.php';
	if(file_exists($file)) {
		include $file;
	}
}
spl_autoload_register('AliOssSdkLoader');
