<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 10/4/14
 * Time: 3:33 PM
 */
namespace CPath\Request\Validation;

use CPath\Request\IRequest;

interface IParameterValidation
{
	/**
	 * Validate and return the parameter value
	 * @param IRequest $Request
	 * @param $value
	 */
	function validateParameter(IRequest $Request, &$value);
}

