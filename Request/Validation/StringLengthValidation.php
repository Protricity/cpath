<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 10/4/14
 * Time: 3:55 PM
 */
namespace CPath\Request\Validation;

use CPath\Request\IRequest;
use CPath\Request\RequestException;

class StringLengthValidation implements IParameterValidation
{
	private $mMin, $mMax;

	public function __construct($min = null, $max = null) {
		$this->mMin = $min;
		$this->mMax = $max;
	}

	/**
	 * Validate and return the parameter value
	 * @param IRequest $Request
	 * @param $value
	 * @throws \CPath\Request\RequestException
	 */
	function validateParameter(IRequest $Request, &$value) {
		$l = strlen($value);
		if ($this->mMin !== null && $l < $this->mMin)
			throw new RequestException("String(%d) must be at least %d character(s) long", $l, $this->mMin);
		if ($this->mMax !== null && $l > $this->mMax)
			throw new RequestException("String(%d) must be no greater than %d character(s) long", $l, $this->mMax);
	}
}