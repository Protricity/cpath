<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 10/4/14
 * Time: 2:19 PM
 */
namespace CPath\Request\Parameter;

use CPath\Request\Exceptions\RequestException;
use CPath\Request\IRequest;

class PasswordConfirmField extends PasswordField
{
	private $mPasswordParamName;

	public function __construct(PasswordField $PasswordParameter, $paramName = null, $required = true, $description = "Confirm password") {
		$this->mPasswordParamName = $PasswordParameter->getFieldName();
		if (!$paramName)
			$paramName = $PasswordParameter->getFieldName() . '_confirmm';
		parent::__construct($paramName, $description, $required);
	}


	/**
	 * Validate the request value and return the validated value
	 * @param IRequest $Request
	 * @param $value
	 * @param null $fieldName
	 * @throws \CPath\Request\Exceptions\RequestException
	 * @return mixed validated value
	 */
	function validate(IRequest $Request, $value = null, $fieldName = null) {
		$value = parent::validate($Request, $value, $fieldName ?: $this->getFieldName());
		$value2 = $Request[$this->mPasswordParamName];
		if ($value !== null && $value2 !== $value)
			throw new RequestException("Password confirmation did not match");

		return static::PASS_BLANK;
	}
}