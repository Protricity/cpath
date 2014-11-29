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

class HTMLEmailField extends HTMLFormField
{
	const INPUT_TYPE = 'email';

	/**
	 * @param null $description
	 * @param String|Array|IAttributes $classList attribute inst, class list, or attribute html
	 * @param null $name
	 * @param bool $value
	 * @internal param bool $checked
	 */
	public function __construct($description = null, $classList = null, $name = null, $value = false) {
		parent::__construct($description, $classList, $name, $value);
	}

	function validate(IRequest $Request, $value = null, $fieldName = null) {
		$EmailValidation = new EmailValidation();
		$value           = $EmailValidation->validate($Request, $value, $fieldName);

		return parent::validate($Request, $value, $fieldName);
	}

	// Static

//	static function get($description = null, $classList = null, $name = null, $value = false) {
//		return new HTMLCheckBoxField($description, $classList, $name, $value);
//	}
}