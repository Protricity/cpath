<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 10/3/14
 * Time: 9:37 PM
 */
namespace CPath\Request\Parameter;

use CPath\Render\HTML\Element\IHTMLInput;
use CPath\Request\Form\IFormRequest;
use CPath\Request\IRequest;
use CPath\Request\Parameter\Exceptions\RequiredFormFieldException;

class RequiredFormField extends FormField
{
	const CSS_CLASS_REQUIRED = 'required';

	public function __construct($paramName, $description = null, $defaultValue = null) {
		parent::__construct($paramName, $description, $defaultValue);
	}

	protected function getHTMLInput(IRequest $Request, IHTMLInput $Input=null) {
		$Input = parent::getHTMLInput($Request, $Attr);
		$Input->setAttribute('required', 'required');
		$Input->addClass(static::CSS_CLASS_REQUIRED);
		return $Input;
	}

	/**
	 * Validate and return the parameter value
	 * @param IRequest $Request
	 * @throws RequiredFormFieldException
	 * @return mixed request value
	 */
	function validateRequest(IRequest $Request) {
		$value = parent::validateRequest($Request);
		if (!$value) {
			if (!$Request instanceof IFormRequest)
				throw new RequiredFormFieldException("Required Form field must come from a form request: " . $this->getFieldName());

			throw new RequiredFormFieldException("Form field is required: " . $this->getFieldName());
		}
		return $value;
	}
}

