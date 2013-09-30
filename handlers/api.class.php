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
use CPath\Build;
use CPath\Builders\RouteBuilder;
use CPath\Exceptions\ValidationException;
use CPath\Handlers\Views\APIInfo;
use CPath\Interfaces\APIFieldNotFound;
use CPath\Interfaces\IAPI;
use CPath\Interfaces\IAPIExecute;
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
use CPath\Model\MultiException;
use CPath\Model\Response;
use CPath\Model\Route;
use CPath\Util;
use CPath\Validate;

/**
 * Class APIHandler
 * @package CPath
 *
 * Provides a Handler template for API calls
 */
abstract class API implements IAPI {

    const BUILD_IGNORE = false;             // API Calls are built to provide routes
    const LOG_ENABLE = true;                // Enable API Logging
    const ROUTE_API_INFO = true;            // Add an APIInfo route entry for this API

    const ROUTE_METHODS = 'GET,POST,CLI';   // Default accepted methods are GET and POST
    const ROUTE_PATH = NULL;                // No custom route path. Path is based on namespace + class name

    /** @var IAPIField[] */
    protected $mFields = array();
    /** @var IAPIValidation[] */
    protected $mValidations = array();
    /** @var SimpleLogger */
    private $mLog = NULL;

    private $mSetup = false;

    private $mRequestProcessed = false;

    public function __construct() {

    }

    /**
     * Execute this API Endpoint with the entire request.
     * This method must call processRequest to validate and process the request object.
     * @param IRequest $Request the IRequest instance for this render which contains the request and args
     * @return IResponse|mixed the api call response with data, message, and status
     */
    abstract protected function doExecute(IRequest $Request);

    /**
     * Set up API fields. Lazy-loaded when fields are accessed
     * @return void
     */
    abstract protected function setupAPI();

    private function _setupFields() {
        if($this->mSetup)
            return;
        $this->mSetup = true;
        $this->setupAPI();
        return;
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
            if($this instanceof IAPIExecute)
                $this->onAPIPreExecute($Request);

            $Response = $this->doExecute($Request);

            if($Response instanceof IResponseAggregate)
                $Response = $Response->createResponse();
            if(!($Response instanceof IResponse))
                $Response = new Response(true, "API executed successfully", $Response);
        } catch (\Exception $ex) {
            if($ex instanceof IResponseAggregate)
                $Response = $ex->createResponse();
            elseif($ex instanceof IResponse)
                $Response = $ex;
            else
                $Response = new ExceptionResponse($ex);
        }
        if($this instanceof IAPIExecute)
            $this->onAPIPostExecute($Request, $Response);
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
        $Render = new APIInfo();
        $Render->renderAPI($this, $Request->getRoute());
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
     * @param IAPIField $Field Describes the Field. Implement IAPIField for custom validation
     * @param boolean|int $prepend Set true to prepend
     * @return $this Return the class instance
     */
    public function addField($name, IAPIField $Field, $prepend=false) {
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
    public function addFields(Array $fields) {
        $this->_setupFields();
        foreach($fields as $name => $Field)
            $this->mFields[$name] = $Field;
        return $this;
    }

    /**
     * Get all API Fields
     * @return IAPIField[]
     */
    public function getFields() {
        $this->_setupFields();
        return $this->mFields;
    }

    /**
     * Get an API field by name
     * @param String $fieldName the field name
     * @return IAPIField
     * @throws APIFieldNotFound if the field was not found
     */
    public function getField($fieldName) {
        $this->_setupFields();
        if(!isset($this->mFields[$fieldName]))
            throw new APIFieldNotFound("Field '{$fieldName}' is not in this API");
        return $this->mFields;
    }

    /**
     * Add a validation
     * @param IAPIValidation $Validation the validation
     * @return $this Return the class instance
     */
    function addValidation(IAPIValidation $Validation){
        $this->mValidations[] = $Validation;
        return $this;
    }

    /**
     * Process a request. Validates each Field. Provides optional Field formatting
     * @param IRequest $Request the IRequest instance for this render which contains the request and args
     * @return void
     * @throws ValidationExceptions if one or more Fields fail to validate
     */
    public function processRequest(IRequest $Request) {
        $this->_setupFields();
        if($this->mRequestProcessed)                        // Ugly. need a better system. Probably should just force processRequest every time
            return;
        if($Request instanceof IShortOptions)
            $Request->processShortOptions(array_keys($this->getFields()));
        if($arg = $Request->getNextArg()) {
            foreach($this->getFields() as $name=>$Field) {
                if($Field instanceof IAPIParam) {
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
                $data[$name] = $Field->validate($Request[$name]);
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

        $this->mRequestProcessed = true;
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
        if(static::ROUTE_API_INFO && !isset($routes['GET']))
            $routes['GET'] = new Route('GET ' . $path, 'CPath\Handlers\Views\APIInfo', get_called_class());
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

interface IAPIValidation {
    /**
     * Validate and return request
     * @param IRequest $Request the pending request to validate
     * @throws ValidationException if a validation exception occurred
     */
    function validate(IRequest $Request);
}

class APIValidation implements IAPIValidation {
    protected $mCallback;
    /**
     * Create an APIValidation using a callback
     * @param Callable $callback which accepts the IRequest $Request for validation
     */
    function __construct($callback) {
        $this->mCallback = $callback;
    }

    /**
     * Validate and return request
     * @param IRequest $Request the pending request to validate
     * @throws ValidationException if a validation exception occurred
     */
    function validate(IRequest $Request) {
        $call = $this->mCallback;
        $call($Request);
    }
}
/**
 * Class IAPIField
 * @package CPath
 * Represents an API Field
 */
interface IAPIField {
    /**
     * Validates an input field. Throws a ValidationException if it fails to validate
     * @param mixed $value the input field to validate
     * @return mixed the formatted input field that passed validation
     * @throws ValidationException if validation fails
     */
    public function validate($value);

    /**
     * @return String a description of the Api Field
     */
    public function getDescription();
}

/**
 * Class APIParam
 * @package CPath
 * This interface tags an API Field as a route parameter.
 */
interface IAPIParam extends IAPIField {

}

/**
 * Class ValidationExceptions
 * @package CPath
 * Throw when one or more Fields fails to validate
 */
class ValidationExceptions extends MultiException {
    public function addFieldException($fieldName, ValidationException $ex) {
        parent::add($ex->getFieldError($fieldName), $fieldName);
    }
}

/**
 * Class APIField
 * @package CPath
 * Represents an 'optional' API Field
 */
class APIField implements IAPIField {
    public $mDescription, $mValidation;
    public function __construct($description=NULL, $validation=0) {
        $this->mDescription = $description;
        $this->mValidation = $validation;
    }

    public function getDescription() {
        return $this->mDescription;
    }

    public function setValidation($filter) {
        $this->mValidation = $filter;
        return $this;
    }

    public function validate($value) {
        if($value === "")
            $value = NULL;
        if($this->mValidation)
            Validate::input($value, $this->mValidation);
        return $value;
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
 * Class APIRequiredField
 * @package CPath
 * Represents a 'required' API Field
 */
class APIRequiredField extends APIField {
    public function validate($value) {
        $value = parent::validate($value);
        if(!$value && $value !== '0')
            throw new RequiredFieldException();
        return $value;
    }
}

/**
 * Class APIParam
 * @package CPath
 * Represents a Parameter from the route path
 */
class APIParam extends APIField implements IAPIParam {
}

/**
 * Class APIRquiredParam
 * @package CPath
 * Represents a Required Parameter from the route path
 */
class APIRequiredParam extends APIRequiredField implements IAPIParam {
}

class APIEnumField extends APIField {
    protected $mEnum;
    public function __construct($description, $_enumValues) {
        parent::__construct($description);
        $this->mEnum = is_array($_enumValues) ? $_enumValues : array_slice(func_get_args(), 1);
    }

    public function validate($value) {
        $value = parent::validate($value);
        if(!in_array($value, $this->mEnum))
            throw new ValidationException("Field '%s' must be one of the following: '" . implode("', '", $this->mEnum) . "'");
        return $value;
    }

    public function getDescription() {
        return $this->getDescription() . ": '" . implode("', '", $this->mEnum) . "'";
    }
}

class APIEnumParam extends APIField implements IAPIParam {}
