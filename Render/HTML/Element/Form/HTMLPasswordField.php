<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 11/28/14
 * Time: 1:13 PM
 */
namespace CPath\Render\HTML\Element\Form;

use CPath\Render\HTML\Attribute\IAttributes;
use CPath\Render\HTML\Element\HTMLInputField;
use CPath\Request\Validation\IValidation;

class HTMLPasswordField extends HTMLInputField
{
	const PASS_BLANK = '*****';
	const INPUT_TYPE = 'password';

	/**
	 * @param String|null $name field name (name=[])
	 * @param String|null $value input value (value=[])
	 * @param String|null|Array|IAttributes|IValidation $_validation [varargs] attribute html as string, array, or IValidation || IAttributes instance
	 * @internal param null|String $classList a list of class elements
	 */
	public function __construct($name = null, $value = null, $_validation = null) {
		parent::__construct($name, $value);

		foreach(func_get_args() as $i => $arg)
			if($i >= 3 || !is_string($arg))
				$this->addVarArg($arg);
	}

}