<?php

class GetTask extends \Phalcon\Cli\Task
{
	public function mainAction()
	{
		echo 'Get what?';
	}

	public function VersionAction()
	{
		cout($this->config->version);
	}

}
