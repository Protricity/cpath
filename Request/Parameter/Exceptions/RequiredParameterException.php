<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 11/1/14
 * Time: 1:57 PM
 */
namespace CPath\Request\Parameter\Exceptions;

use CPath\Request\Parameter\IRequestParameter;

class RequiredParameterException extends ParameterException
{
	public function __construct(IRequestParameter $Parameter, $message=null) {
		parent::__construct($Parameter, $message ?: "Parameter is required: " . $Parameter->getFieldName());
	}
}

