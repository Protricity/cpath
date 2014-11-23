<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 11/1/14
 * Time: 1:58 PM
 */
namespace CPath\Request\Parameter\Exceptions;

use CPath\Request\Parameter\IRequestParameter;

class RequiredFormFieldException extends ParameterException
{
	public function __construct(IRequestParameter $Parameter, $message = null) {
		parent::__construct($Parameter, $message);
	}
}