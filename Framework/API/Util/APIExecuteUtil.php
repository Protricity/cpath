<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Framework\API\Util;

use CPath\Describable\Describable;
use CPath\Describable\IDescribable;
use CPath\Describable\IDescribableAggregate;
use CPath\Framework\API\Exceptions\ValidationException;
use CPath\Framework\API\Exceptions\ValidationExceptions;
use CPath\Framework\API\Field\Collection\Interfaces\IFieldCollection;
use CPath\Framework\API\Field\Interfaces\IField;
use CPath\Framework\API\Field\Util\FieldUtil;
use CPath\Framework\API\Interfaces\IAPI;
use CPath\Framework\CLI\Option\Interfaces\IOptionMap;
use CPath\Framework\CLI\Option\Interfaces\IOptionProcessor;
use CPath\Framework\CLI\Option\Type\OptionMap;
use CPath\Request\IRequest;
use CPath\Framework\Response\Interfaces\IResponse;
use CPath\Framework\Response\Interfaces\IResponseAggregate;
use CPath\Framework\Response\Types\DataResponse;
use CPath\Framework\Response\Types\ExceptionResponse;
use CPath\Interfaces\IExecute;
use CPath\Interfaces\ILogEntry;
use CPath\Interfaces\ILogListener;

class APIExecuteUtil implements IAPI, ILogListener, IDescribableAggregate {
    private $mAPI, $mLoggingEnabled = true, $mLogs = array(), $mMap = null;

    function __construct(IAPI $API) {
        $this->mAPI = $API;
    }

    /**
     * Get the Object Description
     * @return IDescribable|String a describable Object, or string describing this object
     */
    function getDescribable()
    {
        return Describable::get($this->mAPI);
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
     * @param IRequest $Request the IRequest instance for this render which contains the request
     * @internal param Array $args additional arguments for this execution
     * @return IResponse the api call response with data, message, and status
     */
    final public function execute(IRequest $Request) {
        $this->processRequest($Request);
        $Response = $this->mAPI->execute($Request);

        if($Response instanceof IResponseAggregate)
            $Response = $Response->createResponse();
        if(!($Response instanceof IResponse))
            $Response = new DataResponse(true, "API executed successfully", $Response);
        return $Response;
    }


    /**
     * Execute this API Endpoint with the entire request returning an IResponse object
     * @param IRequest $Request the IRequest instance for this render which contains the request and args
     * @return IResponse the api call response with data, message, and status
     */
    final public function executeOrCatch(IRequest $Request) {
        try {
            $Response = $this->execute($Request);
            if($Response->getCode() == IResponse::STATUS_SUCCESS && $this instanceof IExecute)
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
     * @param IRequest $Request the IRequest instance for this render which contains the request and args
     * @throws \CPath\Framework\API\Exceptions\ValidationExceptions
     * @return void
     */
    function processRequest(IRequest $Request) {
        /** @var IField $Field */
        if($Request instanceof IOptionProcessor) {
            if($this->mAPI instanceof IOptionMap)
                $Request->processMap($this->mAPI);
            else
                $Request->processMap($this->generateFieldShorts($Request));
        }

        if($args = $Request->getArgs()) {
            if($args[0])
                foreach($this->mAPI->getFields($Request) as $Field) {
                    $FieldUtil = new FieldUtil($Field);
                    if($FieldUtil->isParam()) {
                        $Request[$Field->getName()] = array_shift($args);
                        if(!$args)
                            break;
                    }
                }
        }

        $FieldExceptions = new ValidationExceptions($this);
        $data = array();
        foreach($this->mAPI->getFields($Request) as $Field) {
            $name = $Field->getName();
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
     * Get all API Fields
     * @param IRequest $Request the IRequest instance for this render which contains the request and args
     * @return IField[]|IFieldCollection
     */
    private function generateFieldShorts(IRequest $Request) {
        $Map = new OptionMap();

        foreach($this->getFields($Request) as $Field)
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
     * @param IRequest $Request the IRequest instance for this render which contains the request and args
     * @return IField[]|IFieldCollection
     */
    function getFields(IRequest $Request) {
        return $this->mAPI->getFields($Request);
    }

//    /**
//     * Get an API field by name
//     * @param String $fieldName the field name
//     * @return IField
//     * @throws \CPath\Framework\API\Exceptions\FieldNotFoundException if the field was not found
//     */
//    public function getField($fieldName) {
//        foreach($this->mAPI->getFields() as $Field) {
//            if($Field->getName() == $fieldName)
//                return $Field;
//        }
//
//        throw new FieldNotFoundException("Field '" . $fieldName . "' was not found");
//    }
}