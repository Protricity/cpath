<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Handlers\Api\Interfaces;

use CPath\Helpers\Describable;
use CPath\Interfaces\IHandler;
use CPath\Interfaces\ILogEntry;
use CPath\Interfaces\IRequest;
use CPath\Interfaces\IRoutable;
use CPath\Model\MultiException;

class APIException extends \Exception {}
class InvalidAPIException extends APIException {}
class FieldNotFound extends APIException {};

/**
 * Class ValidationException
 * @package CPath
 * Thrown when input fails to validate
 */
class ValidationException extends \Exception {
    public function getFieldError($fieldName) {
        return strpos($msg = $this->getMessage(), '%s') !== false
            ? sprintf($msg, $fieldName)
            : $msg;
    }

    /**
     * @param $fieldName
     * @return ValidationException
     */
    public function updateMessage($fieldName) {
        $this->message = $this->getFieldError($fieldName);
        return $this;
    }
}

/**
 * Class RequiredFieldException
 * @package CPath
 * Throw when a required field is missing
 */
class RequiredFieldException extends ValidationException {
    function __construct($msg = "Field '%s' is required") {
        parent::__construct($msg);
    }
}

/**
 * Class ValidationExceptions
 * @package CPath
 * Throw when one or more Fields fails to validate
 */
class ValidationExceptions extends MultiException {
    public function __construct(IAPI $API, $message=NULL) {
        parent::__construct("Errors occurred in API '" . Describable::get($API)->getDescription(). "':\n");
    }

    public function addFieldException($fieldName, ValidationException $ex) {
        parent::add($ex->getFieldError($fieldName), $fieldName);
    }
}


interface IAPI extends IHandler, IRoutable {

    /**
     * Execute this API Endpoint with the entire request.
     * @param IRequest $Request the IRequest instance for this render which contains the request and args
     * @return \CPath\Interfaces\IResponse the api call response with data, message, and status
     */
    function execute(IRequest $Request);

    /**
     * Get all API Fields
     * @return IField[]
     */
    function getFields();

    /**
     * Get an API field by name
     * @param String $fieldName the field name
     * @return IField
     * @throws FieldNotFound if the field was not found
     */
    public function getField($fieldName);

    /**
     * Add a validation
     * @param IValidation $Validation the validation
     * @return $this Return the class instance
     */
    function addValidation(IValidation $Validation);

    /**
     * Enable or disable logging for this IAPI
     * @param bool $enable set true to enable and false to disable
     * @return $this Return the class instance
     */
    function captureLog($enable=true);

    /**
     * Get captured logs
     * @return ILogEntry[]
     */
    function getLogs();
//
//    /**
//     * Process a request. Validates each Field. Provides optional Field formatting
//     * @param IRequest $Request the IRequest instance for this render which contains the request and args
//     * @return void
//     * @throws ValidationExceptions if one or more Fields fail to validate
//     */
//    public function processRequest(IRequest $Request);
}