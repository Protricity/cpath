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
	 * @param null $fieldName
	 * @throws \CPath\Request\Exceptions\RequestException
	 * @return mixed validated value
	 */
	function validate(IRequest $Request, $value, $fieldName = null) {
		$fieldDesc = $fieldName ? "Field '$fieldName'" : 'String';

		$l = strlen($value);
		if ($this->mMin !== null && $l < $this->mMin)
			throw new RequestException(sprintf("%s(%d) must be at least %d character(s) long", $fieldDesc, $l, $this->mMin));
		if ($this->mMax !== null && $l > $this->mMax)
			throw new RequestException(sprintf("%s(%d) must be no greater than %d character(s) long", $fieldDesc, $l, $this->mMax));
		return $value;
	}
}

