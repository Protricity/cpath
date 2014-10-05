<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 10/4/14
 * Time: 4:00 PM
 */
namespace CPath\Request\Validation;

use CPath\Request\IRequest;
use CPath\Request\RequestException;

class RegexValidation implements IParameterValidation
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
	 * @param $value
	 * @throws \CPath\Request\RequestException
	 */
	function validateParameter(IRequest $Request, &$value) {
		if (!preg_match($this->mRegex, $value, $matches))
			throw new RequestException($this->mErrorMsg ? : (self::DEFAULT_MESSAGE . $this->mRegex));
	}
}