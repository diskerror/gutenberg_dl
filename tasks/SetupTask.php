<?php
/**
 * Created by PhpStorm.
 * User: reid
 * Date: 2/23/18
 * Time: 4:54 PM
 */

class SetupTask extends \Phalcon\Cli\Task
{
	public function mysqlAction()
	{
		$this->mysql->query('DROP `pg` IF EXISTS');

		$this->mysql->query('
CREATE TABLE `pg` (
  `id` BIGINT(20) UNSIGNED GENERATED ALWAYS AS (json_extract(`meta`,\'$.id\')) STORED NOT NULL,
  `meta` JSON NOT NULL,
  `author` VARCHAR(2048) GENERATED ALWAYS AS (json_extract(`meta`,\'$.author\')) STORED,
  `title` VARCHAR(4096) GENERATED ALWAYS AS (json_extract(`meta`,\'$.title\')) STORED,
  `language` VARCHAR(512) GENERATED ALWAYS AS (json_extract(`meta`,\'$.language\')) STORED,
  `text` LONGTEXT,
  PRIMARY KEY (`id`),
  KEY `author` (`author`(16)),
  KEY `title` (`title`(16)),
  KEY `language` (`language`(16))
) ENGINE=InnoDB DEFAULT CHARSET=utf8
		');
	}

	public function addIndexAction()
	{
		$this->mysql->query('ALTER TABLE `pg` ADD FULLTEXT KEY `text` (`text`)');
	}
}
