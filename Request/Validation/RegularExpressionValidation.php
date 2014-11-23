<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 11/18/14
 * Time: 3:50 PM
 */
namespace CPath\Request\Validation;

use CPath\Request\Exceptions\RequestException;
use CPath\Request\IRequest;

class RegularExpressionValidation implements IValidation
{

	private $mRegex;
	private $mDescription;

	public function __construct($regex, $description = null) {
		$this->mRegex       = $regex;
		$this->mDescription = $description;
	}

	/**
	 * Validate the request value and return the validated value
	 * @param IRequest $Request
	 * @param $value
	 * @param null $fieldName
	 * @throws \CPath\Request\Exceptions\RequestException
	 * @return mixed validated value
	 */
	function validate(IRequest $Request, $value, $fieldName = null) {
		if (preg_match($this->mRegex, $value, $matches))
			throw new RequestException($this->mDescription ? : "Value must match regex: " . $this->mRegex);

		return $value;
	}
}