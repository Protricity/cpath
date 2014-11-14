<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 11/13/14
 * Time: 5:39 PM
 */
namespace CPath\Request\Common;

use CPath\Request\Exceptions\RequestException;
use CPath\Request\IRequest;

interface IInputField
{
	/**
	 * Get the request value from the field or from the IRequest
	 * @param IRequest $Request
	 * @throws RequestException if the parameter failed validated
	 * @return mixed
	 */
	public function getInputValue(IRequest $Request);

	/**
	 * Get parameter name
	 * @return String
	 */
	public function getFieldName();
}