<?php
/**
 * Created by PhpStorm.
 * User: reid
 * Date: 2/7/18
 * Time: 12:11 PM
 */

class BookFile extends Diskerror\Typed\TypedClass
{

	protected $type = '';

	protected $name = '';

	/**
	 * @param $v
	 */
	protected function _set_type($v)
	{
		if (is_array($v) || is_object($v)) {
			if (is_object($v) ) {
				$v = (array)$v;
			}

			$v = $v['a'];
		}

		$this->type = self::_castToString($v);
	}

	/**
	 * @param $v
	 */
	protected function _set_name($v)
	{
		if (is_array($v) || is_object($v)) {
			if (is_object($v) ) {
				$v = (array)$v;
			}

			$v = $v['a'];
		}

		$this->name = self::_castToString($v);
	}

}
