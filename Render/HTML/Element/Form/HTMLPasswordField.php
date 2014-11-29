<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 11/28/14
 * Time: 1:13 PM
 */
namespace CPath\Render\HTML\Element\Form;

class HTMLPasswordField extends HTMLFormField
{
	const PASS_BLANK = '*****';

	/**
	 * @param null $description
	 * @param null $classList
	 * @param null $name
	 */
	public function __construct($description = null, $classList = null, $name = null) {
		parent::__construct($description, $classList, $name, null, 'password');
	}

	// Static

//	static function get($description = null, $classList = null, $name = null) {
//		return new HTMLPasswordField($description, $classList, $name);
//	}
}