<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 10/3/14
 * Time: 11:14 PM
 */
namespace CPath\Request\Parameter;

use CPath\Request\IRequest;
use CPath\Request\Parameter\RequiredFormField;
use CPath\Request\RequestException;

class PasswordField extends RequiredFormField
{
	const PASS_BLANK = '*****';
	public function __construct($paramName, $description = null) {
		parent::__construct($paramName, $description, self::PASS_BLANK);
		$this->Input->setAttribute('type', 'password');
	}

	/**
	 * Validate and return the parameter value
	 * @param IRequest $Request
	 * @param $value
	 * @throws \CPath\Request\RequestException
	 * @return mixed request value
	 */
	function validate(IRequest $Request, $value) {
		$value = parent::validate($Request, $value);
		if($value === self::PASS_BLANK) {
			$this->Label->addClass(self::CSS_CLASS_ERROR);
			throw new RequestException("Password was not entered");
		}
		return $value;
	}
}