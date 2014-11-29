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
	/** @var IValidation[] */
	private $mValidations = array();

	public function __construct($regex, $errorMsg = null) {
		$this->mRegex    = $regex;
		$this->mErrorMsg = $errorMsg;
	}

	function addValidation(IValidation $Validation, IValidation $_Validation=null) {
		foreach(func_get_args() as $Validation)
			$this->mValidations[] = $Validation;
	}

	/**
	 * Validate the request and return the validated value
	 * @param IRequest $Request
	 * @param $value
	 * @param null $fieldName
	 * @throws \CPath\Request\Exceptions\RequestException
	 * @return mixed validated value
	 */
	function validate(IRequest $Request, $value = null, $fieldName = null) {
		if (!preg_match($this->mRegex, $value, $matches))
			throw new RequestException($this->mErrorMsg ? : (self::DEFAULT_MESSAGE . $this->mRegex));
		/** @var IValidation $Validation */
		foreach($this->mValidations as $Validation)
			$value = $Validation->validate($Request, $value);
		return $value;
	}
}