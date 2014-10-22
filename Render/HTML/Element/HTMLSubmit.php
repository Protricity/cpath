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
	 * @param String|null $text
	 * @param String|null $name
	 * @param String|Array|IAttributes $classList attribute instance, class list, or attribute html
	 * @internal param null|String $value
	 */
	public function __construct($text = 'Submit', $name = null, $classList = null) {
		parent::__construct($text, 'submit', $classList);
		if ($name)
			$this->setName($name);
	}
}