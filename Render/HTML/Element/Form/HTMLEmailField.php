<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 11/28/14
 * Time: 2:33 PM
 */
namespace CPath\Render\HTML\Element\Form;

use CPath\Render\HTML\Attribute\IAttributes;
use CPath\Request\IRequest;
use CPath\Request\Validation\EmailValidation;
use CPath\Request\Validation\IValidation;

class HTMLEmailField extends HTMLFormField
{
	const INPUT_TYPE = 'email';

	/**
	 * @param String|null $classList a list of class elements
	 * @param String|null $name field name (name=[])
	 * @param String|null $value input value (value=[])
	 * @param String|null|Array|IAttributes|IValidation $_validation [varargs] attribute html as string, array, or IValidation || IAttributes instance
	 */
	public function __construct($classList = null, $name = null, $value = null, $_validation = null) {
		parent::__construct($classList, $name, $value);

		foreach(func_get_args() as $i => $arg)
			if($i >= 3 || !is_string($arg))
				$this->addVarArg($arg);
	}

	function validate(IRequest $Request, $value = null, $fieldName = null) {
		$EmailValidation = new EmailValidation();
		$value           = $EmailValidation->validate($Request, $value, $fieldName);

		return parent::validate($Request, $value, $fieldName);
	}

}