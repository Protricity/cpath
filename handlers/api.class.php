<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Handlers;

use CPath\Base;
use CPath\Handlers\Api\Interfaces\APIException;
use CPath\Handlers\Api\Interfaces\FieldNotFound;
use CPath\Handlers\Api\Interfaces\IValidation;
use CPath\Handlers\Api\Interfaces\ValidationException;
use CPath\Handlers\Api\Interfaces\IField;
use CPath\Handlers\Api\Interfaces\ValidationExceptions;
use CPath\Handlers\Views\APIInfo;
use CPath\Handlers\Api\Interfaces\IAPI;
use CPath\Interfaces\IExecute;
use CPath\Interfaces\IBuildable;
use CPath\Interfaces\ILogEntry;
use CPath\Interfaces\IRequest;
use CPath\Interfaces\IResponse;
use CPath\Interfaces\IResponseAggregate;
use CPath\Interfaces\IRoute;
use CPath\Interfaces\IRouteBuilder;
use CPath\Interfaces\IShortOptions;
use CPath\Misc\SimpleLogger;
use CPath\Model\ExceptionResponse;
use CPath\Model\Response;
use CPath\Model\Route;
use CPath\Util;

/**
 * Class APIHandler
 * @package CPath
 *
 * Provides a Handler template for API calls
 */
abstract class API implements IAPI {

    const BUILD_IGNORE = false;             // API Calls are built to provide routes
    const LOG_ENABLE = true;                // Enable API Logging
    const ROUTE_API_INFO = ':api';           // Add an APIInfo route entry i.e. ':api' for this API on GET requests

    const ROUTE_METHODS = 'GET,POST,CLI';   // Default accepted methods are GET and POST
    const ROUTE_PATH = NULL;                // No custom route path. Path is based on namespace + class name

    /** @var IField[] */
    protected $mFields = array();
    /** @var IValidation[] */
    protected $mValidations = array();
    /** @var SimpleLogger */
    private $mLog = NULL;

    private $mSetup = false;

    public function __construct() {

    }

    /**
     * Set up API fields. Lazy-loaded when fields are accessed
     * @return void
     */
    abstract protected function setupAPI();

    /**
     * Execute this API Endpoint with the entire request.
     * @param IRequest $Request the IRequest instance for this render which contains the request and args
     * @return IResponse|mixed the api call response with data, message, and status
     * @throws APIException if an exception occurs
     */
    abstract protected function doExecute(IRequest $Request);


    private function _setupFields() {
        if($this->mSetup)
            return;
        $this->mSetup = true;
        $this->setupAPI();
        return;
    }

    /**
     * Execute this API Endpoint with the entire request returning an IResponse object or throwing an exception
     * @param IRequest $Request the IRequest instance for this render which contains the request and args
     * @return IResponse the api call response with data, message, and status
     */
    final public function executeOrThrow(IRequest $Request) {
        if($this instanceof IExecute)
            $this->onAPIPreExecute($Request);

        $this->_processRequest($Request);
        $Response = $this->doExecute($Request);

        if($Response instanceof IResponseAggregate)
            $Response = $Response->createResponse();
        if(!($Response instanceof IResponse))
            $Response = new Response(true, "API executed successfully", $Response);
        return $Response;
    }


    /**
     * Execute this API Endpoint with the entire request returning an IResponse object
     * @param IRequest $Request the IRequest instance for this render which contains the request and args
     * @return IResponse the api call response with data, message, and status
     */
    final public function execute(IRequest $Request) {
        if(static::LOG_ENABLE)
            $this->mLog = new SimpleLogger(true);
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
        if(static::LOG_ENABLE) {
            foreach($this->mLog->getLogs() as $Log)
                $Response->addLogEntry($Log);
            unset($this->mLog);
        }
        return $Response;
    }

    /**
     * Enable or disable logging for this IAPI
     * @param bool $enable set true to enable and false to disable
     * @param int|NULL $level the log level to capture
     * @return $this Return the class instance
     */
    function captureLog($enable=true, $level=NULL) {
        if($this->mLog)
            $this->mLog->capture($enable, $level=NULL);
    }

    /**
     * Get captured logs
     * @return ILogEntry[]
     */
    function getLogs() {
        if($this->mLog)
            return $this->mLog->getLogs();
        return array();
    }

    /**
     * Sends headers, executes the request, and renders an IResponse as HTML
     * @param IRequest $Request the IRequest instance for this render which contains the request and remaining args
     * @return void
     */
    public function renderHTML(IRequest $Request) {
        if(!headers_sent() && !Base::isCLI())
            header("Content-Type: text/html");
        $Response = null;
        //if(strcasecmp($Request->getMethod(), 'get') !== 0) //TODO: did we decide how to handle posts from a browser?
        //    $Response = $this->execute($Request);
        $Render = new APIInfo();
        $Render->renderAPI($this, $Request->getRoute(), $Request, $Response);
        //$Response = $this->execute($Route);
        //$Response->sendHeaders();
        //$Response->renderHtml();
    }

    /**
     * Sends headers, executes the request, and renders an IResponse as JSON
     * @param IRequest $Request the IRequest instance for this render which contains the request and remaining args
     * @return void
     */
    public function renderJSON(IRequest $Request) {
        if(!headers_sent()) // && !Base::isCLI())
            header("Content-Type: application/json");
        $Response = $this->execute($Request);
        $Response->sendHeaders();
        try{
            $JSON = Util::toJSON($Response);
            echo json_encode($JSON);
        } catch (\Exception $ex) {
            $Response = new ExceptionResponse($ex);
            $JSON = Util::toJSON($Response);
            echo json_encode($JSON);
        }
    }

    /**
     * Sends headers, executes the request, and renders an IResponse as XML
     * @param IRequest $Request the IRequest instance for this render which contains the request and remaining args
     * @return void
     */
    public function renderXML(IRequest $Request) {
        if(!headers_sent()) // && !Base::isCLI())
            header("Content-Type: text/xml");
        $Response = $this->execute($Request);
        $Response->sendHeaders();
        try{
            $XML = Util::toXML($Response);
            echo $XML->asXML();
        } catch (\Exception $ex) {
            $Response = new ExceptionResponse($ex);
            $XML = Util::toXML($Response);
            echo $XML->asXML();
        }
    }

    /**
     * Sends headers, executes the request, and renders an IResponse as Plain Text
     * @param IRequest $Request the IRequest instance for this render which contains the request and remaining args
     * @return void
     */
    public function renderText(IRequest $Request) {
        $Response = $this->execute($Request);
        $Response->sendHeaders('text/plain');
        $Response->renderText();
    }

    /**
     * Renders via default method
     * @param IRequest $Request the IRequest instance for this render which contains the request and remaining args
     * @return void
     */
    public function renderDefault(IRequest $Request) {
        $this->renderText($Request);
    }

    /**
     * Add an API Field.
     * @param $name string name of the Field
     * @param IField $Field Describes the Field. Implement IField for custom validation
     * @param boolean|int $prepend Set true to prepend
     * @return $this Return the class instance
     * @throw \InvalidArgumentException if the field already exist, or is replacing a field that does not exist
     */
    protected function addField($name, IField $Field, $prepend=false) {

        if(isset($this->mFields[$name]))
            throw new \InvalidArgumentException("Field {$name} already exists.");

        if(strpos($name, ':') !== false) {
            list($name, $shorts) = explode(':', $name, 2);
            foreach(explode(';', $shorts) as $short)
                $Field->addShortName($short);
        }
        $Field->setName($name);

        $this->_setupFields();
        if($prepend) {
            $old = $this->mFields;
            $this->mFields = array();
            $this->mFields[$name] = $Field;
            foreach($old as $k=>$v)
                $this->mFields[$k] = $v;
        } else {
            $this->mFields[$name] = $Field;
        }
        return $this;
    }

    /**
     * Add an array of API Fields
     * @param array $fields associative array of Fields.
     * The array key represents the Field name.
     * @return $this return the class instance
     */
    protected function addFields(Array $fields) {
        $this->_setupFields();
        foreach($fields as $name => $Field)
            $this->addField($name, $Field);
        return $this;
    }

    /**
     * Get all API Fields
     * @return IField[]
     */
    public function getFields() {
        $this->_setupFields();
        return $this->mFields;
    }

    /**
     * Get an API field by name
     * @param String $fieldName the field name
     * @return IField
     * @throws FieldNotFound if the field was not found
     */
    public function getField($fieldName) {
        $this->_setupFields();
        if(!isset($this->mFields[$fieldName]))
            throw new FieldNotFound("Field '{$fieldName}' is not in this API");
        return $this->mFields;
    }

    /**
     * Generates short names for all fields that have no short names and returns a list.
     * @return array an associative array of short names
     */
    public function generateFieldShorts() {
        $shorts = array();
        /** @var IField[] $genFields */
        $genFields = array();
        foreach($this->getFields() as $Field) {
            if($list = $Field->getShortNames()) {
                foreach($list as $short)
                    $shorts[$short] = $Field->getName();
            } else {
                $genFields[] = $Field;
            }
        }

        $i=97;
        foreach($genFields as $Field) {
            $short = '';
            foreach(explode('_', $Field->getName()) as $f2)
                $short .= $f2[0];

            $short = strtolower($short);
            if(!isset($shorts[$short])) {
                $shorts[$short] = $Field->getName();
                $Field->addShortName($short);
            } else {
                while(isset($shorts[chr($i)]))
                    $i++;
                if($i>122) break;
                $shorts[chr($i)] = $Field->getName();
                $Field->addShortName($short);
            }
        }

        return $shorts;
    }

    /**
     * Add a validation
     * @param IValidation $Validation the validation
     * @return $this Return the class instance
     */
    function addValidation(IValidation $Validation){
        $this->mValidations[] = $Validation;
        return $this;
    }

    /**
     * Process a request. Validates each Field. Provides optional Field formatting
     * @param IRequest $Request the IRequest instance for this render which contains the request and args
     * @return void
     * @throws ValidationExceptions if one or more Fields fail to validate
     */
    protected function processRequest(IRequest $Request) {
    }

    /**
     * Process a request. Validates each Field. Provides optional Field formatting
     * @param IRequest $Request the IRequest instance for this render which contains the request and args
     * @return void
     * @throws ValidationExceptions if one or more Fields fail to validate
     */
    private function _processRequest(IRequest $Request) {
        $this->_setupFields();
        $this->processRequest($Request);
        if($Request instanceof IShortOptions)
            foreach($this->getFields() as $name => $Field)
                foreach($Field->getShortNames() as $shortName)
                    $Request->processShortOption($name, $shortName);

        if($arg = $Request->getNextArg()) {
            foreach($this->getFields() as $name=>$Field) {
                if($Field->isParam()) {
                    $Request[$name] = $arg;
                    if(!$arg = $Request->getNextArg())
                        break;
                }
            }
        }

        $FieldExceptions = new ValidationExceptions();
        $data = array();
        foreach($this->getFields() as $name=>$Field) {
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

        foreach($this->mValidations as $Validation) {
            try {
                $Validation->validate($Request);
            } catch (ValidationException $ex) {
                $FieldExceptions->addFieldException(null, $ex);  // TODO: null?
            }
        }

        if(count($FieldExceptions))
            throw $FieldExceptions;
    }

    /**
     * Render this API Call. The output format is based on the requested mimeType from the browser
     * @param IRequest $Request the IRequest instance for this render which contains the request and remaining args
     */
    public function render(IRequest $Request) {
        foreach($Request->getMimeTypes() as $mimeType) {
            switch($mimeType) {
                case 'application/json':
                    $this->renderJSON($Request);
                    return;
                case 'application/xml':
                    $this->renderXML($Request);
                    return;
                case 'text/html':
                    $this->renderHTML($Request);
                    return;
                case 'text/plain':
                    $this->renderText($Request);
                    return;
            }
        }
        $this->renderDefault($Request);
    }

    /**
     * Returns an array of all routes for this class
     * @param IRouteBuilder $Builder the IRouteBuilder instance
     * @return IRoute[]
     */
    public function getAllRoutes(IRouteBuilder $Builder) {
        $path = static::ROUTE_PATH ?: $Builder->getHandlerDefaultPath($this);
        $routes = $Builder->getHandlerDefaultRoutes($this, static::ROUTE_METHODS, $path);
        if(static::ROUTE_API_INFO) {
            $token = static::ROUTE_API_INFO;
            $routes['GET ' . $token] = new Route('GET ' . $path . '/' . $token, get_class(new APIInfo()), get_called_class());
            $routes['POST ' . $token] = new Route('POST ' . $path . '/' . $token, get_class(new APIInfo()), get_called_class());
            // TODO: wildcard methods
        }
        return $routes;
    }

    // Statics

    /**
     * Return an instance of the class for building purposes
     * @return IBuildable|NULL an instance of the class or NULL to ignore
     */
    static function createBuildableInstance() {
        return new static;
    }
}


