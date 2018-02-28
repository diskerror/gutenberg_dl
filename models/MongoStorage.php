<?php
/**
 * Created by PhpStorm.
 * User: reid
 * Date: 2/21/18
 * Time: 12:32 PM
 */

class MongoStorage
{
	/**
	 * @var \MongoDB\Collection
	 */
	protected $_collection;

	/**
	 * BookStorage constructor.
	 *
	 * @param \Phalcon\Cli\TaskInterface
	 */
	public function __construct(\Phalcon\Cli\TaskInterface $task)
	{
		$this->_collection = $task->mongo
			->{$task->config->mongo->database}
			->{$task->config->mongo->collection};
	}

	/**
	 * @param \Book $newBook
	 */
	public function insertMerge(Book $newBook)
	{
		$orig = $this->_collection->findOne(['_id' => $newBook->id_]);

		if ($orig === null) {
			$this->_collection->insertOne($newBook->getSpecialObj());
			return;
		}

		$origBook = new Book($orig);
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

		$this->_collection->replaceOne(['_id' => $origBook->id_], $origBook->getSpecialObj());
	}

	/**
	 * Return only the first column of data.
	 *
	 * @param string $columnName
	 * @param array  $filter
	 * @param array  $options
	 *
	 * @return array
	 */
	public function findColumn(string $columnName, array $filter = [], array $options = []): array
	{
		$found = $this->_collection->find($filter, $options);

		$res = [];
		foreach ($found as $f) {
			$res[] = $f[$columnName];
		}

		return $res;
	}

	/**
	 * @return \MongoDB\Collection
	 */
	public function getCollection(): \MongoDB\Collection
	{
		return $this->_collection;
	}
}
