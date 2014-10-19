<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 10/4/14
 * Time: 2:19 PM
 */
namespace CPath\Request\Parameter;

use CPath\Request\IRequest;
use CPath\Request\Exceptions\RequestException;

class PasswordConfirmField extends PasswordField
{
	private $mPassword;

	public function __construct(PasswordField $PasswordParameter, $paramName = null, $required = true, $description = "Confirm password") {
		$this->mPassword = $PasswordParameter;
		if (!$paramName)
			$paramName = $PasswordParameter->getName() . '_confirmm';
		parent::__construct($paramName, $description, $required);
	}


	/**
	 * Validate and return the parameter value
	 * @param IRequest $Request
	 * @return mixed|string
	 * @throws \CPath\Request\Exceptions\RequestException
	 * @internal param $value
	 */
	function validateRequest(IRequest $Request) {
		$value = parent::validateRequest($Request);
		$value2 = $Request->getValue($this->mPassword);
		if ($value !== null && $value2 !== $value)
			throw new RequestException("Password confirmation did not match");

		return static::PASS_BLANK;
	}
}