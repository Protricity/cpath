<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 10/19/14
 * Time: 1:56 PM
 */
namespace CPath\Render\HTML\Element\Form;

use CPath\Render\HTML\Attribute\IAttributes;
use CPath\Render\HTML\Header\IHTMLSupportHeaders;
use CPath\Request\Validation\IValidation;

class HTMLSubmit extends HTMLInputField
{
	/**
	 * @param String|null $name field name (name=[])
	 * @param String|null $value input value (value=[])
	 * @param null $classList
	 * @param String|null|Array|IAttributes|IHTMLSupportHeaders|IValidation $_content [varargs] class as string, array, or IValidation || IAttributes instance
	 */
	public function __construct($name = null, $value = null, $classList=null, $_content = null) {
		parent::__construct($name, $value, 'submit', $classList);

		is_scalar($name)        ? $this->setFieldName($name)    : $this->addVarArg($name);
		is_scalar($value)       ? $this->setInputValue($value)  : $this->addVarArg($value);
		is_scalar($classList)   ? $this->addClass($classList)   : $this->addVarArg($classList);

		foreach(func_get_args() as $arg)
			if(!is_scalar($arg))
				$this->addVarArg($arg);
	}

}