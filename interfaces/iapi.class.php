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

interface IAPI extends IHandler, IRoutable, IDescribable {

    /**
     * Execute this API Endpoint with the entire request.
     * @param IRoute $Route the IRoute instance for this render which contains the request and args
     * @return \CPath\Interfaces\IResponse the api call response with data, message, and status
     */
    function execute(IRoute $Route);

    /**
     * Get all API Fields
     * @return IAPIField[]
     */
    function getFields();

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

    /**
     * Process a request. Validates each Field. Provides optional Field formatting
     * @param IRoute $Route the IRoute instance for this render which contains the request and args
     * @return array the processed and validated request data
     * @throws ValidationExceptions if one or more Fields fail to validate
     */
    public function processRequest(IRoute $Route);
}