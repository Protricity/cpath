<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 10/19/14
 * Time: 1:56 PM
 */
namespace CPath\Render\HTML\Element\Form;

use CPath\Render\HTML\Attribute\IAttributes;

class HTMLSubmit extends HTMLFormField
{
	/**
	 * @param String|Array|IAttributes $classList attribute inst, class list, or attribute html
	 * @param string $value
	 * @param String|null $name
	 */
	public function __construct($classList = null, $value = null, $name = null) {
		parent::__construct($classList, $name, $value, 'submit');
	}

}