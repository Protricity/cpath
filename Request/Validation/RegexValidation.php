<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 10/4/14
 * Time: 4:00 PM
 */
namespace CPath\Request\Validation;

use CPath\Request\IRequest;
use CPath\Request\Exceptions\RequestException;

class RegexValidation implements IRequestValidation
{
	const DEFAULT_MESSAGE = 'Regex Failed: ';
	private $mRegex, $mErrorMsg;

	public function __construct($regex, $errorMsg = null) {
		$this->mRegex    = $regex;
		$this->mErrorMsg = $errorMsg;
	}

	/**
	 * Validate and return the parameter value
	 * @param IRequest $Request
	 * @throws \CPath\Request\Exceptions\RequestException
	 * @internal param $value
	 */
	function validateRequest(IRequest $Request) {
		if (!preg_match($this->mRegex, $value, $matches))
			throw new RequestException($this->mErrorMsg ? : (self::DEFAULT_MESSAGE . $this->mRegex));
	}
}