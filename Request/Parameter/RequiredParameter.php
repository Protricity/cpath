<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/26/14
 * Time: 11:45 PM
 */
namespace CPath\Request\Parameter;

use CPath\Render\HTML\Element\Form\IHTMLFormField;
use CPath\Request\Exceptions\RequestException;
use CPath\Request\IRequest;

class RequiredParameter extends Parameter
{
    const CSS_CLASS_REQUIRED = 'required';

	public function __construct($paramName, $description=null, $defaultValue=null) {
		parent::__construct($paramName, $description, $defaultValue);
	}

	function getHTMLInput(IHTMLFormField $Input=null) {
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
	 * @throws Exceptions\RequiredParameterException
	 * @return mixed validated value
	 */
	function validate(IRequest $Request, $value = null, $fieldName = null) {
		$fieldName = $fieldName ?: $this->getFieldName();
		$value = parent::validate($Request, $value, $fieldName);
		if (!$value)
			throw new RequestException("Parameter is required: " . $fieldName);
		return $value;
	}

}

