<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 11/30/14
 * Time: 11:28 PM
 */
namespace CPath\Render\HTML\Element\Form\Required;

use CPath\Render\HTML\Element\Form\HTMLFormField;
use CPath\Request\IRequest;
use CPath\Request\Validation\RequiredValidation;

class RequiredHTMLFormField extends HTMLFormField
{
	public function __construct($attributes = null, $name = null, $value = null, $type = null) {
		parent::__construct(, $name, $value, $type);
		$this->setAttribute('required', 'required');
	}

	function validate(IRequest $Request, $value = null, $fieldName = null) {
		$Validation = new RequiredValidation();
		$value      = $Validation->validate($Request, $value, $fieldName);

		return parent::validate($Request, $value, $fieldName);
	}
}