#!/usr/bin/env php
<?php

ini_set('display_errors', '1');
error_reporting(E_ALL);

ini_set('memory_limit', 2147483648);

define('APP_PATH', realpath(__DIR__));

require APP_PATH . '/functions/errorHandler.php';
require APP_PATH . '/functions/cli.php';
require APP_PATH . '/vendor/autoload.php';

try {
	(new \Phalcon\Loader())
		->registerDirs([
			APP_PATH . '/tasks/',
			APP_PATH . '/models/',
			APP_PATH . '/structs/',
		])
		->register();

	//	The config files instantiate "$config".
	require APP_PATH . '/functions/config.php';

	$di = new Di($config);

	$arguments = [];
	if (array_key_exists(1, $argv)) {
		$arguments['task'] = $argv[1];

		if (array_key_exists(2, $argv)) {
			$arguments['action'] = $argv[2];

			if (array_key_exists(3, $argv)) {
				$arguments['params'][] = $argv[3];
			}
		}
	}

	(new Phalcon\Cli\Console(new Di($config)))
		->handle($arguments);

}
catch (Throwable $t) {
	cerr("\n" . $t);
}

if (array_key_exists('printNewLine', $config) && $config->printNewLine) {
	cout(PHP_EOL);
}
