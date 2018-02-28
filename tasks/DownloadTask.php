<?php
/**
 * Created by PhpStorm.
 * User: reid
 * Date: 2/19/18
 * Time: 10:24 PM
 */

class DownloadTask extends \Phalcon\Cli\Task
{
	protected $_downloadIndex;

	protected $_downloadText;

	public function mainAction()
	{
		$this->DvdIndexAction();

		cout("\n\n");
		$this->PgIndexAction();

		cout("\n\n");
		$this->GitIndexAction();

		cout("\n\n");
		$this->DvdTextAction();

		cout("\n\n");
		$this->GitTextAction();
	}

	public function DvdIndexAction()
	{
		//	These file are local. Fast access.
		cout("Retrieving from DVD\n");
		$this->_getDownloadIndex()->Dvd('/home/reid/PGDVD_2010_04_RC2');
	}

	protected function _getDownloadIndex()
	{
		if (!isset($this->_downloadIndex)) {
			$this->_downloadIndex = new DownloadIndex($this->mysql);
		}
		return $this->_downloadIndex;
	}

	public function PgIndexAction()
	{
		//	Most up-to-date meta data.
		cout("Project Gutenberg\n");
		$this->_getDownloadIndex()
			 ->Pg('https://raw.githubusercontent.com/hugovk/gutenberg-metadata/master/gutenberg-metadata.json');
	}

	public function GitIndexAction()
	{
		//	Book files are the most up-to-date while being accessable by a machine.
		cout("GITenberg repositories\n");
		$this->_getDownloadIndex()
			 ->Git('https://raw.githubusercontent.com/GITenberg-dev/giten_site/master/assets/GITenberg_repos_list_2.tsv');
	}

	public function DvdTextAction()
	{
		$this->_getDownloadText()->Dvd('/home/reid/PGDVD_2010_04_RC2');
	}

	protected function _getDownloadText()
	{
		if (!isset($this->_downloadText)) {
			$this->_downloadText = new DownloadText($this->mysql);
		}
		return $this->_downloadText;
	}

	public function GitTextAction()
	{
		$this->_getDownloadText()->Git();
	}
}
