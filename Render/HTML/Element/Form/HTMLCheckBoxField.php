<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 11/15/14
 * Time: 6:46 PM
 */
namespace CPath\Render\HTML\Element\Form;

use CPath\Render\HTML\Attribute\IAttributes;

class HTMLCheckBoxField extends HTMLFormField
{

	/**
	 * @param null $description
	 * @param String|Array|IAttributes $classList attribute inst, class list, or attribute html
	 * @param null $name
	 * @param bool $checked
	 */
	public function __construct($description = null, $classList = null, $name = null, $checked = false) {
		parent::__construct($description, $classList, $name, $checked, 'checkbox');
	}

	// Static

//	static function get($description = null, $classList = null, $name = null, $checked = false) {
//		return new HTMLCheckBoxField($description, $classList, $name, $checked);
//	}
}
