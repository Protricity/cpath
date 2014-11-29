<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 11/27/14
 * Time: 1:22 AM
 */
namespace CPath\Request\Validation;

use CPath\Request\IRequest;

class ValidationCallback implements IValidation
{
	private $mCallback;

	public function __construct($callback) {
		$this->mCallback = $callback;
	}

	/**
	 * Validate the request value and return the validated value
	 * @param IRequest $Request
	 * @param $value
	 * @param null $fieldName
	 * @throw Exception if validation failed
	 * @return mixed validated value
	 */
	function validate(IRequest $Request, $value = null, $fieldName = null) {
		$callback = $this->mCallback;

		return $callback($Request, $value, $fieldName);
	}
}