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

class HTMLCheckBoxField extends HTMLInputField
{
	const INPUT_TYPE = 'checkbox';

	/**
	 * @param String|null $name field name (name=[])
	 * @param bool|null $checked
	 * @param null $value
	 * @param String|null $classList a list of element classes
	 * @param String|null|Array|IAttributes|IValidation $_validation [varargs] attribute html as string, array, or IValidation || IAttributes instance
	 * @internal param null|String $classList a list of class elements
	 */
	public function __construct($name = null, $checked = false, $value = null, $classList = null, $_validation = null) {
		parent::__construct($name);
		if(is_bool($checked) && $checked)
			$this->setChecked($checked);

		is_scalar($name)        ?: $this->addVarArg($name);
		is_scalar($value)       ? $this->setAttribute('value', $value) : $this->addVarArg($value);
		is_scalar($checked)     ?: $this->addVarArg($checked);
		is_scalar($classList)   ? $this->addClass($classList)   : $this->addVarArg($classList);

		for($i=3; $i<func_num_args(); $i++)
			$this->addVarArg(func_get_arg($i));
	}

	public function setInputValue($value) {
		$this->setChecked($value);
	}

	public function setChecked($checked=true) {
		$checked
		? $this->setAttribute('checked', 'checked')
		: $this->removeAttribute('checked');
	}
}
