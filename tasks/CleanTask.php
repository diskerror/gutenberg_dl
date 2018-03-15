<?php
/**
 * Created by PhpStorm.
 * User: reid
 * Date: 2018-03-12_12:10:28
 */

class CleanTask extends \Phalcon\Cli\Task
{

	public function mainAction()
	{
		(new CleanText($this->mysql))
			->CleanAll();
	}
}
