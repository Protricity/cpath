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

class HTMLFileInputField extends HTMLFormField
{
	const INPUT_TYPE = 'file';

	/**
	 * @param String|null $classList a list of class elements
	 * @param String|null $name field name (name=[])
	 * @param String|null $accept
	 * @param String|null|Array|IAttributes|IValidation $_validation [varargs] attribute html as string, array, or IValidation || IAttributes instance
	 */
	public function __construct($classList = null, $name = null, $accept = null, $_validation = null) {
		parent::__construct($classList, $name);
		if(is_string($accept))
			$this->setAccept($accept);

		foreach(func_get_args() as $i => $arg)
			if($i >= 3 || !is_string($arg))
				$this->addVarArg($arg);
	}

	public function setAccept($accept) {
		$this->setAttribute('accept', $accept);
	}

	// Static

//	static function get($description = null, $classList = null, $name = null) {
//		return new HTMLFileInputField($description, $classList, $name);
//	}
}