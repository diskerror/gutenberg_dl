<?php
/**
 * Created by PhpStorm.
 * User: reid
 * Date: 2/23/18
 * Time: 4:30 PM
 */

use Zend\Json\Json;
use Diskerror\Typed\ArrayOptions;

class MysqlStorage extends Phalcon\Db\Adapter\Pdo\Mysql
{
	/**
	 * @param \Book $newBook
	 */
	public function mergeUpdate(Book $newBook)
	{
		$orig = $this->fetchOne('SELECT meta FROM pg WHERE id = ' . $newBook->id);

		if ($orig === false) {
			$newBook->setArrayOptions(ArrayOptions::OMIT_EMPTY);
			$this->query('
				INSERT INTO pg
				SET meta = "' . addslashes(Json::encode($newBook->toArray())) . '"
			');
			return;
		}

		$origBook = new Book(json_decode($orig['meta']));

		$newBook->setArrayOptions(ArrayOptions::OMIT_EMPTY);
		$newBookSO = $newBook->toArray();

		foreach ($newBookSO as $k => &$v) {
			switch ($k) {
				case 'author':
				case 'title':
				case 'language':
				case 'subject':
				case 'contributor':
				case 'note':
				case 'file':
					foreach ($v as $vm) {
						if (!in_array($vm, $origBook->{$k}->getContainerReference())) {
							$origBook->{$k}[] = $vm;
						}
					}
				break;

				case 'release_date':
				case 'copyright':
				case 'updated':
				case 'gitb':
					$origBook->{$k} = $newBookSO[$k];
			}
		}

		$this->query('
			UPDATE pg
			SET meta = "' . addslashes(Json::encode($origBook->toArray())) . '"
			WHERE id  = ' . $origBook->id
		);
	}

}
