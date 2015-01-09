<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 10/19/14
 * Time: 1:56 PM
 */
namespace CPath\Render\HTML\Element\Form;

class HTMLSubmit extends HTMLInputField
{
	/**
	 * @param String|null $name field name (name=[])
	 * @param String|null $value input value (value=[])
	 * @internal param null|String $classList a list of class elements
	 */
	public function __construct($name = null, $value = null) {
		parent::__construct($name, $value, 'submit');
	}

}