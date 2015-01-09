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

class HTMLPasswordField extends HTMLInputField
{
	const PASS_BLANK = '*****';
	const INPUT_TYPE = 'password';

	/**
	 * @param String|null $name field name (name=[])
	 * @param String|null $classList a list of element classes
	 * @param String|null|Array|IAttributes|IValidation $_validation [varargs] attribute html as string, array, or IValidation || IAttributes instance
	 */
	public function __construct($name = null, $classList = null, $_validation = null) {
		parent::__construct($name);

		is_string($name)        ?: $this->addVarArg($name);
		is_string($classList)   ? $this->addClass($classList)   : $this->addVarArg($classList);

		for($i=1; $i<func_num_args(); $i++)
			$this->addVarArg(func_get_arg($i));
	}

}