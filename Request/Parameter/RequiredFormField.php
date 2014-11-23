<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 10/3/14
 * Time: 9:37 PM
 */
namespace CPath\Request\Parameter;

use CPath\Request\Form\IFormRequest;
use CPath\Request\IRequest;
use CPath\Request\Parameter\Exceptions\RequiredFormFieldException;

class RequiredFormField extends FormField
{
	const CSS_CLASS_REQUIRED = 'required';

	public function __construct($paramName, $description = null, $defaultValue = null) {
		parent::__construct($paramName, $description, $defaultValue);
	}

	function getHTMLInput() {
		$Input = parent::getHTMLInput();
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
