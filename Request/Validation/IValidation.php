<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 11/10/14
 * Time: 2:19 PM
 */
namespace CPath\Request\Validation;

use CPath\Request\IRequest;

interface IValidation
{
	/**
	 * Validate the request value and return the validated value
	 * @param IRequest $Request
	 * @param $value
	 * @throw Exception if validation failed
	 * @return mixed validated value
	 */
	function validate(IRequest $Request, $value);
}