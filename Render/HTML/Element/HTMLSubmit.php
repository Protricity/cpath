<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 10/19/14
 * Time: 1:56 PM
 */
namespace CPath\Render\HTML\Element;

use CPath\Render\HTML\Attribute\IAttributes;

class HTMLSubmit extends HTMLInputField
{
	/**
	 * @param String|null $name
	 * @param string $value
	 * @param String|Array|IAttributes $classList attribute inst, class list, or attribute html
	 */
	public function __construct($name = null, $value = 'Submit', $classList = null) {
		parent::__construct($name, $value, 'submit', $classList);
		if ($name)
			$this->setFieldName($name);
	}
}