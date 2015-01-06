<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 11/15/14
 * Time: 6:46 PM
 */
namespace CPath\Render\HTML\Element\Form;

use CPath\Render\HTML\Attribute\IAttributes;
use CPath\Render\HTML\Element\HTMLInputField;
use CPath\Request\Validation\IValidation;

class HTMLCheckBoxField extends HTMLInputField
{
	const INPUT_TYPE = 'checkbox';

	/**
	 * @param String|null $name field name (name=[])
	 * @param bool|null $checked
	 * @param String|null|Array|IAttributes|IValidation $_validation [varargs] attribute html as string, array, or IValidation || IAttributes instance
	 * @internal param null|String $classList a list of class elements
	 */
	public function __construct($name = null, $checked = false, $_validation = null) {
		parent::__construct($name);
		if(is_bool($checked) && $checked)
			$this->setChecked($checked);

		foreach(func_get_args() as $i => $arg)
			if($i >= 3 || !is_string($arg))
				$this->addVarArg($arg);
	}

	public function setChecked($checked=true) {
		$checked
		? $this->setAttribute('checked', 'checked')
		: $this->removeAttribute('checked');
	}
}
