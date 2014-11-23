<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 10/4/14
 * Time: 4:00 PM
 */
namespace CPath\Request\Validation;

use CPath\Request\Exceptions\RequestException;
use CPath\Request\IRequest;

class RegexValidation implements IValidation
{
	const DEFAULT_MESSAGE = 'Regex Failed: ';
	private $mRegex, $mErrorMsg;

	public function __construct($regex, $errorMsg = null) {
		$this->mRegex    = $regex;
		$this->mErrorMsg = $errorMsg;
	}


	/**
	 * Validate the request and return the validated value
	 * @param IRequest $Request
	 * @param $value
	 * @param null $fieldName
	 * @throws \CPath\Request\Exceptions\RequestException
	 * @return mixed validated value
	 */
	function validate(IRequest $Request, $value, $fieldName = null) {
		if (!preg_match($this->mRegex, $value, $matches))
			throw new RequestException($this->mErrorMsg ? : (self::DEFAULT_MESSAGE . $this->mRegex));
		return $value;
	}
}