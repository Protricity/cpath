<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 11/15/14
 * Time: 6:46 PM
 */
namespace CPath\Render\HTML\Element;

use CPath\Render\HTML\Attribute\IAttributes;

class HTMLCheckBoxField extends HTMLInputField
{
	const NODE_TYPE = 'checkbox';

	/**
	 * @param null $name
	 * @param null $value
	 * @param null $type
	 * @param String|Array|IAttributes $classList attribute inst, class list, or attribute html
	 */
	public function __construct($name = null, $value = null, $type = null, $classList = null) {
		parent::__construct($name, $value, $type, $classList);
	}

}