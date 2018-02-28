<?php
/**
 * Created by PhpStorm.
 * User: reid
 * Date: 2/23/18
 * Time: 4:30 PM
 */

use Zend\Json\Json;

class MysqlStorage extends Phalcon\Db\Adapter\Pdo\Mysql
{
	/**
	 * @param \Book $newBook
	 */
	public function mergeUpdate(Book $newBook)
	{
		$orig = $this->fetchOne('select meta from books where id = ' . $newBook->id);

		if ($orig === false) {
			$this->query('
insert into books
set meta = "' . addslashes(Json::encode($newBook->getSpecialObj(['dateToBsonDate' => false]))) . '"
			');
			return;
		}

		$origBook = new Book(json_decode($orig['meta']));

		$newBookSO = $newBook->getSpecialObj();

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
update books
set meta = "' . addslashes(Json::encode($origBook->getSpecialObj(['dateToBsonDate' => false]))) . '"
where id  = ' . $origBook->id
		);
	}

}
