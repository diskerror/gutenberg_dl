<?php
/**
 * Created by PhpStorm.
 * User: reid
 * Date: 2018-03-08
 */

/**
 * Class CleanText
 */
class CleanText extends RemovePgText
{
	/**
	 * @var \MongoStorage
	 */
	protected $_connection;

	/**
	 * CleanText constructor.
	 *
	 * @param \Phalcon\Db\Adapter\Pdo\Mysql $connection
	 */
	public function __construct(Phalcon\Db\Adapter\Pdo\Mysql $connection)
	{
		$this->_connection = $connection;
		parent::__construct();
	}

	public function CleanAll()
	{
		cout("Cleaning all text\nRetrieving IDs ");
		$records = $this->_connection->fetchAll('
			SELECT id
			FROM books
			WHERE character_length(`text`) > 3000
			ORDER BY id
		');

		cout("\n");
		foreach ($records as $record) {
			cout(sprintf("\r%d ", $record['id']));

			$text = $this->_connection->fetchColumn('
				SELECT `text`
				FROM books
				WHERE id = ' . $record['id']
			);

			$text = $this->exec($text);

			$this->_connection->query('
				UPDATE books
				SET `text` = "' . addslashes($text) . '"
				WHERE id = ' . $record['id']
			);
		}
	}
}
