<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 10/4/14
 * Time: 3:33 PM
 */
namespace CPath\Request\Validation;

use CPath\Request\IRequest;

interface IRequestValidation
{
	/**
	 * Validate the request and return the validated content
	 * @param IRequest $Request
	 * @return mixed validated content
	 */
	function validateRequest(IRequest $Request);
}

