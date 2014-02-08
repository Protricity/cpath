<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Framework\Api\Interfaces;

use CPath\Framework\CLI\Option\Interfaces\IOptionMap;
use CPath\Framework\CLI\Option\Interfaces\IOptionProcessor;
use CPath\Framework\CLI\Option\Type\OptionMap;
use CPath\Interfaces\IExecute;
use CPath\Interfaces\ILogEntry;
use CPath\Interfaces\ILogListener;
use CPath\Framework\Request\Interfaces\IRequest;
use CPath\Response\ExceptionResponse;
use CPath\Response\IResponse;
use CPath\Response\IResponseAggregate;
use CPath\Response\Response;

class APIUtil implements IAPIUtil, ILogListener {
    private $mAPI, $mLoggingEnabled = true, $mLogs = array(), $mMap = null;

    function __construct(IAPI $API) {
        $this->mAPI = $API;
    }

    /**
     * @return IOptionMap
     */
    public function getOptionMap() {
        if($this->mMap)
            return $this->mMap;

        if($this->mAPI instanceof IOptionMap)
            return $this->mMap = $this->mAPI;

        return $this->mMap = new OptionMap();
    }

    /**
     * @return IAPI
     */
    public function getAPI() {
        return $this->mAPI;
    }

    /**
     * Execute this API Endpoint with the entire request returning an IResponse object or throwing an exception
     * @param IRequest $Request the IRequest instance for this render which contains the request and args
     * @return IResponse the api call response with data, message, and status
     */
    final public function executeOrThrow(IRequest $Request) {
        $this->processRequest($Request);
        $Response = $this->mAPI->execute($Request);

        if($Response instanceof IResponseAggregate)
            $Response = $Response->createResponse();
        if(!($Response instanceof IResponse))
            $Response = new Response(true, "API executed successfully", $Response);
        return $Response;
    }


    /**
     * Execute this API Endpoint with the entire request returning an IResponse object
     * @param \CPath\Framework\Request\Interfaces\IRequest $Request the IRequest instance for this render which contains the request and args
     * @return IResponse the api call response with data, message, and status
     */
    final public function execute(IRequest $Request) {
        try {
            $Response = $this->executeOrThrow($Request);
            if($Response->getStatusCode() == IResponse::STATUS_SUCCESS && $this instanceof IExecute)
                $this->onAPIPostExecute($Request, $Response);
        } catch (\Exception $ex) {
            if($ex instanceof IResponseAggregate)
                $Response = $ex->createResponse();
            elseif($ex instanceof IResponse)
                $Response = $ex;
            else
                $Response = new ExceptionResponse($ex);
        }

        return $Response;
    }


    /**
     * Process a request. Validates each Field. Provides optional Field formatting
     * @param \CPath\Framework\Request\Interfaces\IRequest $Request the IRequest instance for this render which contains the request and args
     * @return void
     * @throws ValidationExceptions if one or more Fields fail to validate
     */
    function processRequest(IRequest $Request) {
        /** @var IField $Field */
        if($Request instanceof IOptionProcessor) {
            if($this->mAPI instanceof IOptionMap)
                $Request->processMap($this->mAPI);
            else
                $Request->processMap($this->generateFieldShorts());
        }

        if($arg = $Request->getNextArg()) {
            foreach($this->mAPI->getFields() as $name=>$Field) {
                $FieldUtil = new FieldUtil($Field);
                if($FieldUtil->isParam()) {
                    $Request[$name] = $arg;
                    if(!$arg = $Request->getNextArg())
                        break;
                }
            }
        }

        $FieldExceptions = new ValidationExceptions($this);
        $data = array();
        foreach($this->mAPI->getFields() as $name=>$Field) {
            try {
                $value = $Field->validate($Request, $name);
                $data[$name] = $value;
            } catch (ValidationException $ex) {
                $FieldExceptions->addFieldException($name, $ex);
                $data[$name] = NULL;
            }
        }
        $Request->merge($data, true);

        if(count($FieldExceptions))
            throw $FieldExceptions;
    }

    /**
     * Generates short names for all fields that have no short names and returns a list.
     * @return IOptionMap an associative array of short names
     */
    private function generateFieldShorts() {
        $Map = new OptionMap();

        foreach($this->getFields() as $Field)
            $Map->addShortByField($Field->getName());

        return $Map;
    }

    /**
     * Enable or disable logging for this IAPI
     * @param bool $enable set true to enable and false to disable
     * @return $this Return the class instance
     */
    function enableLog($enable = true) {
        $this->mLoggingEnabled = $enable;
        return $this;
    }

    /**
     * Get captured logs
     * @return ILogEntry[]
     */
    function getLogs() {
        return $this->mLogs;
    }

    /**
     * Add a log entry
     * @param ILogEntry $Log
     * @return void
     */
    function onLog(ILogEntry $Log) {
        if($this->mLoggingEnabled)
            $this->mLogs[] = $Log;
    }

    /**
     * Get all API Fields
     * @return IField[]
     */
    function getFields() {
        return $this->mAPI->getFields();
    }

    /**
     * Get an API field by name
     * @param String $fieldName the field name
     * @return IField
     * @throws FieldNotFound if the field was not found
     */
    public function getField($fieldName) {
        foreach($this->mAPI->getFields() as $Field) {
            if($Field->getName() == $fieldName)
                return $Field;
        }

        throw new FieldNotFound("Field '" . $fieldName . "' was not found");
    }
}