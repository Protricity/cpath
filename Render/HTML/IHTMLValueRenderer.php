<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 1/31/2015
 * Time: 4:57 PM
 */
namespace CPath\Render\HTML;

interface IHTMLValueRenderer
{
	/**
	 * @param $key
	 * @param $value
	 * @return bool if true, the value has been rendered, otherwise false
	 */
	function renderNamedValue($key, $value);

	/**
	 * @param $value
	 * @return bool if true, the value has been rendered, otherwise false
	 */
	function renderValue($value);
}