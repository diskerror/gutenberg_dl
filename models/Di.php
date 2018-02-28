<?php

use \Phalcon\Mvc\Dispatcher as PhDispatcher;

class Di extends Phalcon\Di\FactoryDefault\Cli
{
	function __construct(\Phalcon\Config $config)
	{
		parent::__construct();

		$this->setShared('config', function() use ($config) {
			return $config;
		});

		$this->setShared('mongo', function() use ($config) {
			static $mongo;
			if (!isset($mongo)) {
				$mongo = new MongoDB\Client($config->mongo->host);
			}
			return $mongo;
		});

		$this->setShared('mysql', function() use ($config) {
			static $connection;
			if (!isset($connection)) {
				$connection = new MysqlStorage((array)$config->mysql);
			}
			return $connection;
		});

	}

}
