<?php
/**
 * Created by PhpStorm.
 * User: reid
 * Date: 2018-02-21_11:57:25
 */

class DownloadIndex
{
	/**
	 * @var \MongoStorage
	 */
	protected $_connection;

	/**
	 * DownloadIndex constructor.
	 *
	 * @param $connection
	 */
	public function __construct($connection)
	{
		$this->_connection = $connection;
	}

	/**
	 * @param string $fileName
	 */
	public function Git(string $fileName)
	{
		cout("Downloading list");
		$list = file_get_contents($fileName);

		cout("\nParsing list");
		$list = strtr($list, ["\r\n" => "\n", '\'' => '&APOS;']);
// 		$list = mb_convert_encoding($list, 'UTF-8', 'ISO-8859-1');
		$list = preg_replace_callback(
			'/(\\t"[^\\t]+"\\t)/',
			function($match) {
				return strtr($match[0], ["\n" => '<BR>']);
			},
			$list
		);

		$data = explode("\n", $list);
		foreach ($data as &$d) {
			$d = str_getcsv($d, "\t");
			if (count($d) > 4) {
				$d[3] = strtr($d[3], ['<BR>' => "\n", '&APOS;' => '\'']);
			}
		}

		//  first row has the keys
		$keys = array_shift($data);

		//  we don't use the first column
		array_shift($keys);
		$countKeys = count($keys);

		cout("\nLoading into DB");

		$this->_connection->query('SET autocommit=0');
		$loopNum = 0;

		foreach ($data as &$v) {
			array_shift($v);
			if ($countKeys !== count($v)) {
				cerr("\nbad line");
				continue;
			}

			$v = @array_combine($keys, $v);
			if ($v['title'] === '' || $v['gitb_id'] < 1) {
				continue;
			}

			$b = new Book();
			$b->id = $v['gitb_id'];
			$b->gitb->name = $v['gitb_name'];
			$b->title[] = $v['title'];
			$b->language[] = $v['language'];

			//	remove square brackets and change to an array
			$textFiles = explode(',', substr($v['text_files'], 1, -1));
			$idLen = strlen((string)$b->id);
			foreach ($textFiles as $f) {
				$f = trim($f);

				switch (substr($f, $idLen)) {
					case '-8.txt':
						$t = 'text/plain';
					break;

					case '.txt':
						$t = 'text/plain; charset="us-ascii"';
					break;

					default:
						switch (strtolower(substr($f, -4))) {
							case '.htm':
							case 'html':
								$t = 'text/html';
							break;

							default:
								$t = '';
							break;
						}
					break;
				}

				$b->gitb->file[] = ['type' => $t, 'name' => $f];
			}

			$this->_connection->mergeUpdate($b);

			if ($loopNum++ > 99) {
				$loopNum = 0;
				$this->_connection->query('COMMIT');
				$this->_connection->query('SET autocommit=0');
			}
		}

		$this->_connection->query('COMMIT');
	}

	/**
	 * @param string $fileName
	 */
	public function Dvd(string $fileName)
	{
		cout("Retrieving files");
		$files = glob($fileName . '/ETEXT/*.HTML');

		$this->_connection->query('SET autocommit=0');
		$loopNum = 0;

		cout("\nParsing and loading each file");
		foreach ($files as $file) {
			$dom = new DOMDocument('1.0', 'UTF-8');
			$dom->loadHTMLFile($file);

			//	table[0] holds the meta data, table[1] holds the file names
			$tables = $dom->getElementsByTagName('table');
			$metaRows = $tables[0]->getElementsByTagName('tr');
			$fileRows = $tables[1]->getElementsByTagName('tr');

			$book = new Book();
			$book->updated = '2010-04-10 13:57:00';

			//	get meta data
			foreach ($metaRows as $row) {
				$field = preg_replace(['/[- ]/', '/[^a-z_]/'], ['_', ''],
					strtolower($row->getElementsByTagName('th')[0]->textContent));
				$data = $row->getElementsByTagName('td')[0]->textContent;

				switch ($field) {
					case 'etext_no':
					case 'release_date':
//					case 'copyright_status':
						$book->{$field} = $data;
					break;

					case 'author':
						$book->{$field}[] = preg_replace('/, \\d{4}-(?:\\d{4}|)$/', '', $data);
					break;

					case 'title':
					case 'language':
					case 'subject':
					case 'contributor':
					case 'note':
						$book->{$field}[] = $data;
					break;
				}
			}

			//	get source files, first row are column titles
			for ($r = 1; $r < $fileRows->length; ++$r) {
				$td = $fileRows[$r]->getElementsByTagName('td');
				$book->file[] = [$td[0]->textContent, $td[1]->textContent];
			}

			$this->_connection->mergeUpdate($book);

			//	commit data only after every 100 records
			if ($loopNum++ > 99) {
				$loopNum = 0;
				$this->_connection->query('COMMIT');
				$this->_connection->query('SET autocommit=0');
			}
		}

		$this->_connection->query('COMMIT');
	}

	/**
	 * @param string $fileName
	 */
	public function Pg(string $fileName)
	{
		cout("Downloading metadata");
		$metaData = file_get_contents($fileName);

		cout("\nDecoding");
		$metaData = strtr($metaData, ["\r\n" => "\n"]);
		$data = Zend\Json\Json::Decode($metaData);

		cout("\nLoading into DB");

		$this->_connection->query('SET autocommit=0');
		$loopNum = 0;

		foreach ($data as $k => &$v) {
			$b = new Book($v);
			$b->id = $k;

			if (count($b->title) === 0) {
				continue;
			}

			$this->_connection->mergeUpdate($b);

			if ($loopNum++ > 99) {
				$loopNum = 0;
				$this->_connection->query('COMMIT');
				$this->_connection->query('SET autocommit=0');
			}
		}

		$this->_connection->query('COMMIT');
	}

}
