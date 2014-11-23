<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 11/23/14
 * Time: 9:00 AM
 */
namespace CPath\Request\Validation;

class UserNameValidation extends RegexValidation
{
	const MIN_USER_LENGTH = 5;
	const MAX_USER_LENGTH = 24;

	public function __construct($allowUsernameChars = '_-', $minLength = self::MIN_USER_LENGTH, $maxLength = self::MAX_USER_LENGTH) {
		$regex = '/^[\w' . $allowUsernameChars . ']+$/';
		parent::__construct($regex, "Username must only contain alphanumeric characters" . ($allowUsernameChars ? ' or [' . $allowUsernameChars . ']' : ''));
		$this->addValidation(
			new StringLengthValidation($minLength, $maxLength)
		);
	}
}
