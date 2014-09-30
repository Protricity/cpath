<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/26/14
 * Time: 11:40 PM
 */
namespace CPath\Request\Validation;

use CPath\Render\HTML\Attribute\IAttributes;
use CPath\Request\IRequest;

interface IValidateRequest
{
    /**
     * Validate this request or throw a ValidationException
     * @param IRequest $Request
     * @return mixed
     * @throws ValidationException if the validation fails
     */
    function validateRequest(IRequest $Request);

    /**
     * @param $message
     * @return \Exception
     */
    //function asException($message);

    /**
     * @param IRequest $Request
     * @param IAttributes $Attr
     */
    //function renderForm(IRequest $Request, IAttributes $Attr = null);
}

