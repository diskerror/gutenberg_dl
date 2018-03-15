<?php
/**
 * Created by PhpStorm.
 * User: reid
 * Date: 2018-02-21_11:57:25
 */

//  https://raw.githubusercontent.com/GITenberg/Song-waves_26916/master/26916-8.txt

/**
 * Class DownloadText
 */
class DownloadText
{
	/**
	 * @var \MongoStorage
	 */
	protected $_connection;

	/**
	 * @var array
	 */
	protected $_available_encodings;

	protected $_cleaner;

	/**
	 * DownloadIndex constructor.
	 *
	 * @param $connection
	 */
	public function __construct($connection)
	{
		$this->_connection = $connection;

		//	Encodings in order of preference.
		$this->_available_encodings = array_flip([
			'text/plain; charset="utf-8"',
			'text/plain; charset="iso-8859-1"',
			'text/plain; charset="utf-16"',
			'text/plain',    //	guess iso-8859-1
			'text/plain; charset="iso-8859-2"',
			'text/plain; charset="iso-8859-3"',
			'text/plain; charset="iso-8859-4"',
			'text/plain; charset="iso-8859-7"',
			'text/plain; charset="iso-8859-15"',
			'text/plain; charset="ibm437"',
			'text/plain; charset="ibm850"',
			'text/plain; charset="macintosh"',
			'text/plain; charset="windows-1250"',
			'text/plain; charset="windows-1251"',
			'text/plain; charset="windows-1252"',
			'text/plain; charset="windows-1253"',
			'text/html; charset="utf-8"',
			'text/html; charset="iso-8859-1"',
			'text/plain; charset="us-ascii"',
			'text/html; charset="us-ascii"',
			'text/html',    //	guess iso-8859-1
			'text/html; charset="windows-1251"',
			'text/html; charset="windows-1252"',
			'text/html; charset="windows-1253"',
			'text/plain, text/html; charset="us-ascii"',
			'',
		]);

		$this->_cleaner = new RemovePgText();
	}

	public function Git()
	{
		$this->_connection->query('SET autocommit=1');

		cout("Downloading GITenberg text");
		//	Get list of appropriate IDs with associated file names.
		$records = $this->_connection->fetchAll('
			SELECT id, meta->"$.gitb" AS `gitb`
			FROM books
			WHERE json_contains_path(meta, "one", "$.gitb")
				AND `text` IS NULL
				AND (json_extract(meta, "$.language") LIKE "%English%" OR json_extract(meta, "$.language") LIKE "%en%")
			ORDER BY id
		');

		foreach ($records as $record) {
			$gitb = new GitB($record['gitb']);

			//	find the best file type available then save it's name and type
			$encNum = 100;
			$name = '';
			$type = 'text/plain; charset="iso-8859-1"';    //	if type is empty then this is used
			foreach ($gitb->file as $file) {
				if (array_key_exists($file->type,
						$this->_available_encodings) && $this->_available_encodings[$file->type] < $encNum) {
					$encNum = $this->_available_encodings[$file->type];
					$name = $file->name;
					$type = $file->type;
				}
			}

			try {
				cout("\n" . $gitb->name . ' > ' . $name);
				$text =
					file_get_contents('https://raw.githubusercontent.com/GITenberg/' . $gitb->name . '/master/' . $name);
			}
			catch (Throwable $t) {
				cout(' - 404?');
				continue;
			}

			$text = strtr($text, ["\r\n" => "\n"]);

			$enc = [];
			if (substr($type, 0, 9) === 'text/html') {
				preg_match('/charset="?(.+?)"/i', $text, $enc);
				$dom = new DOMDocument('1.0', $enc[1]);
				$dom->loadHtml($text);
				$text = $dom->getElementsByTagName('body')[0]->textContent;
			}
			else {
				preg_match('/\\nCharacter set encoding: (.*)\\n/', $text, $enc);
			}


			if (!isset($enc[1])) {
				$enc = 'ISO-8859-1';
			}
			elseif ($enc[1] === 'ISO LATIN-1' || !in_array($enc[1], mb_list_encodings())) {
				$enc = 'ISO-8859-1';
			}
			else {
				$enc = strtoupper($enc[1]);
			}

			cout(' > ' . $enc);

			$text = mb_convert_encoding($text, 'UTF-8', $enc);
			$text = trim($text, "\x00..\x20") . "\n";
			$text = preg_replace('/\n\n\n+/', "\n\n", $text);
			$text = $this->_cleaner->exec($text);

			$this->_connection->query('UPDATE books SET `text` = "' . addslashes($text) . '" WHERE id = ' . $record['id']);
		}
	}

	public function Dvd(string $dirName)
	{
		$this->_connection->query('SET autocommit=1');

		cout("Loading local text files");

		//	Get list of appropriate IDs with associated file names.
		$records = $this->_connection->fetchAll('
			SELECT id, meta->"$.file" AS `file`
			FROM books
			WHERE json_contains_path(meta, "one", "$.file")
				AND `text` IS NULL
				AND (json_extract(meta, "$.language") LIKE "%English%" OR json_extract(meta, "$.language") LIKE "%en%")
			ORDER BY id
		');

		foreach ($records as $record) {
			$files = new Diskerror\Typed\TypedArray($record['file'], 'BookFile');

			//	find the best file type available then save it's name and type
			$encNum = 100;
			$name = '';
			$type = '';
			foreach ($files as $file) {
				if (array_key_exists($file->type,
						$this->_available_encodings) && $this->_available_encodings[$file->type] < $encNum) {
					$encNum = $this->_available_encodings[$file->type];
					$name = strtoupper($file->name);
					$type = $file->type;
				}
			}

			if ($name === '') {
				continue;
			}

			$typeParts = explode('; ', $type);

			//	Make sure the text encoding is understandable.
			if (!isset($typeParts[1])) {
				$typeParts[1] = 'charset="iso-8859-1"';
			}

			switch ($typeParts[1]) {
				case 'charset="ibm437"':
				case 'charset="macintosh"':
					$enc = 'ISO-8859-1';    //	doesn't really work...?
				break;

				case 'charset="ibm850"':
					$enc = 'CP850';
				break;

				case 'charset="windows-1251"':
					$enc = 'Windows-1251';
				break;

				case 'charset="windows-1252"':
					$enc = 'Windows-1252';
				break;

				default:
					$enc = strtoupper(preg_replace('/charset="(?:us-|)(.+?)"/', '$1', $typeParts[1]));
				break;
			}

			//	Get the contents of the file and convert to UTF-8.
			switch (strtoupper(substr($name, -4))) {
				case '.ZIP':
					$escapedName = escapeshellarg($dirName . $name);
					$info = explode("\n", shell_exec('zipinfo -1 ' . $escapedName));
					natsort($info);

					$text = '';
					foreach ($info as $i) {
						$tmp = '';
						switch (strtoupper(substr($i, -4))) {
							case '.TXT':
								$tmp = shell_exec('unzip -p ' . $escapedName . ' ' . escapeshellarg($i)) . "\n\n";
								$tmp = mb_convert_encoding($tmp, 'UTF-8', $enc);
							break;

							case '.HTM':
							case 'HTML':
								$tmp = shell_exec('unzip -p ' . $escapedName . ' ' . escapeshellcmd($i));

								if ($tmp === '') {
									continue;
								}

								$dom = new DOMDocument('1.0', $enc[1]);
								libxml_use_internal_errors(true);
								$dom->loadHtml($tmp);
								$tmp = $dom->getElementsByTagName('body')[0]->textContent;
							break;
						}

						$text .= $tmp . "\n\n";
					}
				break;

				case '.TXT':
					$text = file_get_contents($dirName . $name);
					$text = mb_convert_encoding($text, 'UTF-8', $enc);
				break;

				case '.HTM':
				case 'HTML':
					$text = file_get_contents($dirName . $name);
					$dom = new DOMDocument('1.0', $enc[1]);
					$dom->loadHtml($text);
					$text = $dom->getElementsByTagName('body')[0]->textContent;
				break;

				default:
					continue;
			}

			$text = strtr($text, ["\r\n" => "\n"]);
			$text = preg_replace('/\\n\\n\\n+/', "\n\n", $text);
			$text = trim($text, "\x00..\x20") . "\n";
			$text = $this->_cleaner->exec($text);

			$this->_connection->query('UPDATE books SET `text` = "' . addslashes($text) . '" WHERE id = ' . $record['id']);
		}
	}
}
