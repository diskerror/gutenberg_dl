<?php
/**
 * Created by PhpStorm.
 * User: reid
 * Date: 2018-03-08
 */

/**
 * Class CleanText
 */
class RemovePgText
{
	protected $_regexStartOnly;

	protected $_regexStart;

	protected $_regexEnd;

	/**
	 * RemovePgText constructor.
	 */
	public function __construct()
	{
		$startOnly = [
			'***The Project Gutenberg',
			'*!!!It reflects the mindset of the turn of the last century!!!*',
			'15 October 1991',
			'computers we used then didn\'t have lower case at all.',
			'Gutenberg.  The first was released',
			'Urbana, IL 61801-3231',
			"to header material.\n\n***",
		];

		//	Some strings from:
		//	https://github.com/c-w/gutenberg/blob/master/gutenberg/_domain_model/text.py
		$startMarks = [
			'                this Project Gutenberg edition.',
			'          This is the Project Gutenberg',
			'      (http://www.ibiblio.org/gutenberg/',
			'      http://archive.org/details/',
			' -->',
			' *** START OF THIS PROJECT GUTENBERG',
			'["Small Print" V.',
			'[Project Gutenberg has reassembled the file', //
			'[Updater\'s note',
			'*** START OF THE COPYRIGHTED',
			'*** START OF THE PROJECT GUTENBERG',
			'*** START OF THIS PROJECT GUTENBERG',
			'****     SMALL PRINT!',
			'*****These eBooks Were Prepared By Thousands of Volunteers!*****',
			'*****This file should be named',
			'***START OF THE PROJECT GUTENBERG',
			'***START** SMALL PRINT',
			'**The Project Gutenberg',
			'*END THE SMALL PRINT',
			'*END*THE SMALL PRINT',
			'*SMALL PRINT!',
			'*The Project Gutenberg',
			'#=======',
			'=-=-=-=-=-=',
			'and the Project Gutenberg Online Distributed Proofreading Team',
			'Beginning of this Project Gutenberg',
			'by The Internet Archive)',
			'by The Internet Archive/American Libraries',
			'by The Internet Archive/Canadian Libraries',
			'Distributed Proofreading Team',
			'E-text prepared by',
			'Edited by ',
			'Gutenberg Distributed Proofreaders',
			'Gutenberg Online Distributed',
			'http://gallica.bnf.fr)',
			'http://gutenberg.spiegel.de/ erreichbar.',
			'http://gutenberg2000.de erreichbar.',
			'http://www.pgdp.net',
			'Internet Archive)',
			'Internet Archive/American Libraries',
			'Internet Archive/Canadian Libraries',
			'l\'authorization à les utilizer pour preparer ce texte.',
			'Mary Meehan, and the Project Gutenberg Online Distributed Proofreading',
			'material from the Google Print project',
			'More information about this book is at the top of this file.',
			'of the etext through OCR.',
			'Produced by',
			'Project Gutenberg Distributed Proofreaders',
			'Project Gutenberg Online Distributed',
			'Project Gutenberg TEI',
			'Project Runeberg publishes',
			'Proofreading Team',
			'public domain material from the Internet Archive',
			'tells you about restrictions in how the file may be used.',
			'The ***Copyrighted*** Project Gutenberg',
			'the Project Gutenberg Online Distributed Proofreading Team',
			'the Project Gutenberg Online Distributed',
			'The Project Gutenberg',
			'The Small Print',
			'This eBook was prepared by',
			'This etext was prepared by',
			'This Etext was prepared by',
			'This etext was produced by',
			'This Project Gutenberg Etext was prepared by',
			'Updates to this eBook were provided by',
		];

		$endMarks = [
			'        ***END OF THE PROJECT GUTENBERG',
			' *** END OF THIS PROJECT GUTENBERG',
			' End of the Project Gutenberg',
			'*** END OF THE COPYRIGHTED',
			'*** END OF THE PROJECT GUTENBERG',
			'*** END OF THIS PROJECT GUTENBERG',
			'***END OF THE PROJECT GUTENBERG',
			'**This is a COPYRIGHTED Project Gutenberg Etext, Details Above**',
			'#=======',
			'by Project Gutenberg',
			'Ce document fut presente en lecture',
			'Ce document fut présenté en lecture',
			'End of Project Gutenberg',
			'END OF PROJECT GUTENBERG',
			'End of the Project Gutenberg',
			'End of The Project Gutenberg',
			'End of this is COPYRIGHTED',
			'End of this Project Gutenberg',
			'Ende diese Project Gutenberg',
			'Ende dieses Etextes ',
			'Ende dieses Project Gutenber',
			'Ende dieses Project Gutenberg',
			'Ende dieses Projekt Gutenberg',
			'Fin de Project Gutenberg',
			'More information about this book is at the top of this file.',
			'The Project Gutenberg Etext of ',
			'We need your donations more than ever!',
		];

		array_walk($startOnly, 'RemovePgText::_pregQuote');
		array_walk($startMarks, 'RemovePgText::_pregQuote');
		array_walk($endMarks, 'RemovePgText::_pregQuote');

		$this->_regexStartOnly = '/.+(?:\\n' . implode('|\\n', $startOnly) . ')[^\\n]*[\\n ]*\\n+(.+?)/us';
		$this->_regexStart = '/.+(?:\\n' . implode('|\\n', $startMarks) . ')[^\\n]*\\n\\n?[\\n ]*\\n+(.+?)/us';
		$this->_regexEnd = '/(.+?)\s*?(?:\\n' . implode('|\\n', $endMarks) . ').+/us';
	}

	protected static function _pregQuote(&$in)
	{
		$in = preg_quote($in, '/');
	}

	public function exec($text)
	{
		$split = strlen($text);
		$split = ($split > 14000) ? 14000 : $split;

		$text2 = preg_replace($this->_regexStartOnly, '$1', substr($text, 0, $split)) . substr($text, $split);
		if ($text2 !== $text) {
			return $text2;
		}

		$split = (int)(strlen($text) * 0.4);
		$split = ($split > 12500) ? 12500 : $split;

		$sub = substr($text, 0, $split);
		$subNew = preg_replace($this->_regexStart, '$1', $sub);

		if ($sub !== $subNew) {
			$text = $subNew . substr($text, $split);
		}
		else {
			$text = preg_replace($this->_regexStart, '$1', substr($text, 0, 21000)) . substr($text, 21000);
		}

		return substr($text, 0, -21000) . preg_replace($this->_regexEnd, '$1', substr($text, -21000)) . "\n";
	}
}
