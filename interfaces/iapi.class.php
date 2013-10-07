<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Interfaces;

use CPath\Handlers\IAPIField;
use CPath\Handlers\IAPIValidation;
use CPath\Handlers\ValidationExceptions;

class InvalidAPIException extends \Exception {}
class APIFieldNotFound extends InvalidAPIException {};

interface IAPI extends IHandler, IRoutable, IDescribable {

    /**
     * Execute this API Endpoint with the entire request.
     * @param IRequest $Request the IRequest instance for this render which contains the request and args
     * @return \CPath\Interfaces\IResponse the api call response with data, message, and status
     */
    function execute(IRequest $Request);

    /**
     * Get all API Fields
     * @return IAPIField[]
     */
    function getFields();

    /**
     * Get an API field by name
     * @param String $fieldName the field name
     * @return IAPIField
     * @throws APIFieldNotFound if the field was not found
     */
    public function getField($fieldName);

    /**
     * Add an API Field.
     * @param string $name name of the Field
     * @param IAPIField $Field Describes the Field. Implement IAPIField for custom validation
     * @param boolean|int $prepend Set true to prepend
     * @return $this Return the class instance
     */
    function addField($name, IAPIField $Field, $prepend=false);

    /**
     * Add a validation
     * @param IAPIValidation $Validation the validation
     * @return $this Return the class instance
     */
    function addValidation(IAPIValidation $Validation);

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