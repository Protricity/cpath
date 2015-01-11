<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 11/28/14
 * Time: 2:06 PM
 */
namespace CPath\Render\HTML\Element\Form;

use CPath\Render\HTML\Attribute\IAttributes;
use CPath\Request\Validation\IValidation;

class HTMLFileInputField extends HTMLInputField
{
	const INPUT_TYPE = 'file';

	/**
	 * @param String|null $name field name (name=[])
	 * @param String|null $accept
	 * @param String|null $classList a list of element classes
	 * @param String|null|Array|IAttributes|IValidation $_validation [varargs] attribute html as string, array, or IValidation || IAttributes instance
	 * @internal param null|String $classList a list of class elements
	 */
	public function __construct($name = null, $accept = null, $classList = null, $_validation = null) {
		parent::__construct($name);
		is_scalar($name)        ? $this->setFieldName($name)    : $this->addVarArg($name);
		is_scalar($accept)      ? $this->setInputValue($accept) : $this->addVarArg($accept);
		is_scalar($classList)   ? $this->addClass($classList)   : $this->addVarArg($classList);

		for($i=3; $i<func_num_args(); $i++)
			$this->addVarArg(func_get_arg($i));
	}

	public function setAccept($accept) {
		$this->setAttribute('accept', $accept);
	}

	// Static

//	static function get($description = null, $classList = null, $name = null) {
//		return new HTMLFileInputField($description, $classList, $name);
//	}
}