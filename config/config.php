<?php

/**
 * All nested arrays are converted to nested Phalcon\Config objects.
 *
 * To add to or override these values
 * create another file in this directory
 * that ends in '.php' with contents like:
 *
 */

$config = new \Phalcon\Config([

	'version'      => '0.1',

	/**
	 * CLI: if true, then we print a new line at the end of each execution
	 */
	'printNewLine' => true,

	'mongo' => [
		'host'       => 'mongodb://localhost:27017',
		'database'   => 'books',
		'_collection' => 'books',
	],

	'mysql' => [
		"host"     => "127.0.0.1",
		"username" => "pg",
		"password" => "getbooks",
		"dbname"   => "books",
		"port"     => "3306",
	],

]);
