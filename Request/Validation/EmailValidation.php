<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 11/23/14
 * Time: 3:24 PM
 */
namespace CPath\Request\Validation;

use CPath\Request\Exceptions\RequestException;
use CPath\Request\IRequest;

class EmailValidation implements IValidation
{
	/**
	 * Validate the request value and return the validated value
	 * @param IRequest $Request
	 * @param $value
	 * @param null $fieldName
	 * @throws \CPath\Request\Exceptions\RequestException
	 * @return mixed validated value
	 */
	function validate(IRequest $Request, $value, $fieldName = null) {
		$value = filter_var($value, FILTER_VALIDATE_EMAIL);
		if (!$value)
			throw new RequestException("Invalid email format");

		return $value;
	}
}