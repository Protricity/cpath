<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 10/4/14
 * Time: 3:55 PM
 */
namespace CPath\Request\Validation;

use CPath\Request\Exceptions\RequestException;
use CPath\Request\IRequest;

class StringLengthValidation implements IValidation
{
	private $mMin, $mMax;

	public function __construct($min = null, $max = null) {
		$this->mMin = $min;
		$this->mMax = $max;
	}

	/**
	 * Validate the request and return the validated value
	 * @param IRequest $Request
	 * @param $value
	 * @throws \CPath\Request\Exceptions\RequestException
	 * @return mixed validated value
	 */
	function validate(IRequest $Request, $value) {
		$l = strlen($value);
		if ($this->mMin !== null && $l < $this->mMin)
			throw new RequestException("String(%d) must be at least %d character(s) long", $l, $this->mMin);
		if ($this->mMax !== null && $l > $this->mMax)
			throw new RequestException("String(%d) must be no greater than %d character(s) long", $l, $this->mMax);
		return $value;
	}
}

class RegularExpressionValidation implements IValidation {

	private $mRegex;
	private $mDescription;
	public function __construct($regex, $description=null) {
		$this->mRegex = $regex;
		$this->mDescription = $description;
	}

	/**
	 * Validate the request value and return the validated value
	 * @param IRequest $Request
	 * @param $value
	 * @throws \CPath\Request\Exceptions\RequestException
	 * @return mixed validated value
	 */
	function validate(IRequest $Request, $value) {
		if(preg_match($this->mRegex, $value, $matches))
			throw new RequestException($this->mDescription ?: "Value must match regex: " . $this->mRegex);
		return $value;
	}
}

