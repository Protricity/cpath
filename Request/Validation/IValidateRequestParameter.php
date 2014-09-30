<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/28/14
 * Time: 3:35 PM
 */
namespace CPath\Request\Validation;

use CPath\Request\IRequest;

interface IValidateRequestParameter
{
    /**
     * Validate this request parameter or throw an IRequestValidationException
     * @param IRequest $Request
     * @param $paramName
     * @return mixed the validated parameter value
     * @throws IRequestValidationException if the validation fails
     */
    function validateParameter(IRequest $Request, $paramName);
}