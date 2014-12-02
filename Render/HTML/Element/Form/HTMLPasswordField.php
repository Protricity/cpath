<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 11/28/14
 * Time: 1:13 PM
 */
namespace CPath\Render\HTML\Element\Form;

use CPath\Render\HTML\Attribute\IAttributes;
use CPath\Request\Validation\IValidation;

class HTMLPasswordField extends HTMLFormField
{
	const PASS_BLANK = '*****';
	const INPUT_TYPE = 'password';

	/**
	 * @param String|null $classList a list of class elements
	 * @param String|null $name field name (name=[])
	 * @param String|null $value input value (value=[])
	 * @param String|null|Array|IAttributes|IValidation $_validation [varargs] attribute html as string, array, or IValidation || IAttributes instance
	 */
	public function __construct($classList = null, $name = null, $value = null, $_validation = null) {
		parent::__construct($classList, $name, $value);

		foreach(func_get_args() as $i => $arg)
			$this->addVarArg($arg, $i>=3);
	}

	// Static

//	static function get($description = null, $classList = null, $name = null) {
//		return new HTMLPasswordField($description, $classList, $name);
//	}
}