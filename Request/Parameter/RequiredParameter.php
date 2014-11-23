<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/26/14
 * Time: 11:45 PM
 */
namespace CPath\Request\Parameter;

use CPath\Render\HTML\IRenderHTML;
use CPath\Request\IRequest;
use CPath\Request\Parameter\Exceptions\RequiredParameterException;

class RequiredParameter extends Parameter implements IRenderHTML
{
    const CSS_CLASS_REQUIRED = 'required';

	public function __construct($paramName, $description=null, $defaultValue=null) {
		parent::__construct($paramName, $description, $defaultValue);
	}

	function getHTMLInput() {
		$Input = parent::getHTMLInput();
		$Input->setAttribute('required', 'required');
		$Input->addClass(static::CSS_CLASS_REQUIRED);
		return $Input;
	}

//	/**
//	 * Get the request value
//	 * @throws RequiredParameterException if the parameter failed to validate
//	 * @return mixed
//	 */
//	function getValue() {
//		$value = parent::getValue();
//		if (!$value)
//			throw new RequiredParameterException("Parameter is required: " . $this->getName());
//		return $value;
//
//	}

	/**
	 * Validate the request value and return the validated value
	 * @param IRequest $Request
	 * @param $value
	 * @param null $fieldName
	 * @throws Exceptions\RequiredParameterException
	 * @return mixed validated value
	 */
	function validate(IRequest $Request, $value, $fieldName = null) {
		$value = parent::validate($Request, $value, $fieldName ?: $this->getFieldName());
		if (!$value)
			throw new RequiredParameterException($this, "Parameter is required: " . $this->getFieldName());
		return $value;
	}

	// Static

	static function tryRequired(IRequest $Request, $paramName, $paramDescription=null, $defaultValue=null) {
		$Parameter = new RequiredParameter($paramName, $paramDescription, $defaultValue);
		return $Parameter->getRequestValue($Request);
	}
}

