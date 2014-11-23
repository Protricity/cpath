<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 11/18/14
 * Time: 1:51 AM
 */
namespace CPath\Request\Parameter;

use CPath\Render\HTML\Element\IHTMLInput;
use CPath\Request\Form\IFormRequest;
use CPath\Request\IRequest;
use CPath\Request\Parameter\Exceptions\RequiredFormFieldException;

class RequiredTextAreaField extends TextAreaField
{
	const CSS_CLASS_REQUIRED = 'required';

	function getHTMLInput(IHTMLInput $Input=null) {
		$Input = parent::getHTMLInput($Input);
		$Input->setAttribute('required', 'required');
		$Input->addClass(static::CSS_CLASS_REQUIRED);

		return $Input;
	}


	/**
	 * Validate the request value and return the validated value
	 * @param IRequest $Request
	 * @param $value
	 * @param null $fieldName
	 * @throws Exceptions\RequiredFormFieldException
	 * @return mixed validated value
	 */
	function validate(IRequest $Request, $value, $fieldName = null) {
		$value = parent::validate($Request, $value, $fieldName ?: $this->getFieldName());
		if (!$value) {
			if (!$Request instanceof IFormRequest)
				throw new RequiredFormFieldException($this, "Required Form field must come from a form request: " . $this->getFieldName());

			throw new RequiredFormFieldException($this, "Form field is required: " . $this->getFieldName());
		}

		return $value;
	}
}