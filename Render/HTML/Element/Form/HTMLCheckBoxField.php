<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 11/15/14
 * Time: 6:46 PM
 */
namespace CPath\Render\HTML\Element\Form;

use CPath\Render\HTML\Attribute\IAttributes;
use CPath\Request\Validation\IValidation;

class HTMLCheckBoxField extends HTMLFormField
{
	const INPUT_TYPE = 'checkbox';

	/**
	 * @param String|null $classList a list of class elements
	 * @param String|null $name field name (name=[])
	 * @param bool|null $checked
	 * @param String|null|Array|IAttributes|IValidation $_validation [varargs] attribute html as string, array, or IValidation || IAttributes instance
	 */
	public function __construct($classList = null, $name = null, $checked = false, $_validation = null) {
		parent::__construct($classList, $name, $checked);

		foreach(func_get_args() as $i => $arg)
			$this->addVarArg($arg, $i>=3);
	}
}
