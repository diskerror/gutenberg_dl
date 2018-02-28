<?php
/**
 * Created by PhpStorm.
 * User: reid
 * Date: 2/7/18
 * Time: 12:11 PM
 */

class Book extends Diskerror\Typed\TypedClass
{
	protected $_map = [
		'_id'              => 'id',    //	from Mongo
		'id_'              => 'id',
		'etext_no'         => 'id',    //	from Project Gutenberg
		'bookid'           => 'id',    //	from GITenberg
//		'copyright_status' => 'copyright',
//		'rights'           => 'copyright',
	];

	protected $id = 0;

	protected $author = '__class__Diskerror\Typed\TypedArray(null, "string")';

	protected $title = '__class__Diskerror\Typed\TypedArray(null, "string")';

	protected $language = '__class__Diskerror\Typed\TypedArray(null, "string")';

	protected $subject = '__class__Diskerror\Typed\TypedArray(null, "string")';

	protected $release_date = '__class__Diskerror\Utilities\Date("now")';

//	protected $copyright = '';

	protected $contributor = '__class__Diskerror\Typed\TypedArray(null, "string")';

	protected $note = '__class__Diskerror\Typed\TypedArray(null, "string")';

	protected $file = '__class__Diskerror\Typed\TypedArray(null, "BookFile")';

	protected $updated = '__class__Diskerror\Utilities\DateTime("now")';

	protected $gitb = '__class__GitB()';

//	protected $text = '';
}
