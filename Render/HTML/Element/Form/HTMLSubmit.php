<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 10/19/14
 * Time: 1:56 PM
 */
namespace CPath\Render\HTML\Element\Form;

class HTMLSubmit extends HTMLFormField
{
	/**
	 * @param String|null $classList a list of class elements
	 * @param String|null $name field name (name=[])
	 * @param String|null $value input value (value=[])
	 */
	public function __construct($classList = null, $value = null, $name = null) {
		parent::__construct($classList, $name, $value, 'submit');
	}

}