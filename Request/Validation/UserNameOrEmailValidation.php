<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 11/23/14
 * Time: 3:20 PM
 */
namespace CPath\Request\Validation;

use CPath\Request\Exceptions\RequestException;
use CPath\Request\IRequest;

class UserNameOrEmailValidation extends UserNameValidation
{
	public function __construct($allowUsernameChars = '_-', $minLength = self::MIN_USER_LENGTH, $maxLength = self::MAX_USER_LENGTH) {
		parent::__construct($allowUsernameChars, $minLength, $maxLength);
	}

	function validate(IRequest $Request, $value, $fieldName = null) {
		$EmailValidation = new EmailValidation();
		try {
			$value = $EmailValidation->validate($Request, $value, $fieldName);
			return $value;
		} catch (RequestException $ex) {

		}
		return parent::validate($Request, $value, $fieldName);
	}


}