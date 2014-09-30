<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/20/14
 * Time: 6:54 PM
 */
namespace CPath\Request\Validation;

use CPath\Render\HTML\Attribute\IAttributes;
use CPath\Render\HTML\Attribute;
use CPath\Render\HTML\Element\HTMLForm;
use CPath\Render\HTML\IHTMLContainer;
use CPath\Render\HTML\IRenderHTML;
use CPath\Request\IRequest;
use CPath\Response\IResponse;
use CPath\Response\IResponseCode;

class ValidationForm extends HTMLForm implements IValidateRequest
{
    private $mExceptions = array();
    private $mAllowGET;

    public function __construct($allowGET=false) {
        $this->mAllowGET = $allowGET;
        parent::__construct();
    }

    /**
     * Validate this request or throw a ValidationException
     * @param IRequest $Request
     * @return mixed
     * @throws ValidationException if the validation fails
     */
    function validateRequest(IRequest $Request)   {
        $this->mExceptions = array();
        foreach($this as $Content) {
            if(!$Content instanceof IValidateRequest)
                continue;
            try {
                $Content->validateRequest($Request);
            } catch (IValidationException $ex) {
                $this->mExceptions[] = $ex;
            }
        }
        if(!$this->mAllowGET && $Request->getMethodName() === 'GET') {
            throw new ValidationException("Request method 'GET' not allowed", $this);
        }
        if($this->mExceptions) {
            throw new ValidationException("Form validation failed (" . sizeof($this->mExceptions) . ")", $this);
        }
        return $this;
    }

//    /**
//     * @param $message
//     * @return ValidationFormResponse
//     */
//    function asException($message) {
//        return new ValidationFormResponse($this, $message, IResponseCode::STATUS_ERROR);
//    }
//
//    /**
//     * @param $message
//     * @return ValidationFormResponse
//     */
//    function asResponse($message) {
//        return new ValidationFormResponse($this, $message, IResponseCode::STATUS_SUCCESS);
//    }
}

