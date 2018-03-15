<?php


class MainTask extends \Phalcon\Cli\Task
{
	public function mainAction()
	{
		cout('working');

		cout("\n" . preg_quote('\n'));
	}

}
