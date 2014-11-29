<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 11/26/14
 * Time: 5:04 PM
 */
namespace CPath\Request\Validation;

use CPath\Request\IRequest;

interface IRequestValidation
{
	/**
	 * Validate the request
	 * @param IRequest $Request
	 * @throw Exception if validation failed
	 * @return array|void optionally returns an associative array of modified field names and values
	 */
	function validateRequest(IRequest $Request);
}