<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 11/28/14
 * Time: 1:22 PM
 */
namespace CPath\Request\Validation;

use CPath\Render\HTML\Attribute\Attributes;
use CPath\Render\HTML\Attribute\IAttributes;
use CPath\Render\HTML\Attribute\IAttributesAggregate;
use CPath\Request\Exceptions\RequestException;
use CPath\Request\IRequest;

class RequiredValidation implements IValidation, IAttributesAggregate
{
	/**
	 * Validate the request value and return the validated value
	 * @param IRequest $Request
	 * @param $value
	 * @param null $fieldName
	 * @throws \CPath\Request\Exceptions\RequestException
	 * @return mixed validated value
	 */
	function validate(IRequest $Request, $value = null, $fieldName = null) {
		if (!$value) {
			throw new RequestException("Parameter is required: " . $fieldName); }
	}

	/**
	 * @return IAttributes
	 */
	function getAttributes() {
		return new Attributes('required', 'required');
	}
}