<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Handlers;
use CPath\Handlers\Views\APIInfo;
use CPath\Interfaces\IAPI;
use CPath\Interfaces\IResponseAggregate;
use CPath\Interfaces\IResponseHelper;
use CPath\Interfaces\IRoute;
use CPath\Interfaces\IRouteBuilder;
use CPath\Model\ArrayObject;
use CPath\Model\Route;
use CPath\Util;
use CPath\Build;
use CPath\Interfaces\IResponse;
use CPath\Interfaces\IHandler;
use CPath\Model\MultiException;
use CPath\Model\Response;
use CPath\Model\ResponseException;
use CPath\Builders\RouteBuilder;

/**
 * Class APIHandler
 * @package CPath
 *
 * Provides a Handler template for API calls
 */
abstract class API implements IAPI {

    const Build_Ignore = false;     // API Calls are built to provide routes

    const Route_Methods = 'GET|POST|CLI';     // Default accepted methods are GET and POST
    const Route_Path = NULL;        // No custom route path. Path is based on namespace + class name

    /** @var IAPIField[] */
    protected $mFields = array();
    /** @var IRoute */
    protected $mRoute=NULL;

    /**
     * Execute this API Endpoint with the entire request.
     * This method must call processRequest to validate and process the request object.
     * @param array $request associative array of request Fields, usually $_GET or $_POST
     * @return IResponse|mixed the api call response with data, message, and status
     */
    abstract function execute(Array $request);

    /**
     * Execute this API Endpoint with the entire request returning an IResponse object
     * @param array $request associative array of request Fields, usually $_GET or $_POST
     * @return IResponse the api call response with data, message, and status
     */
    public function executeAsResponse(Array $request) {
        try {
            $Response = $this->execute($request);
            if($Response instanceof IResponseAggregate)
                $Response = $Response->getResponse();
            if(!($Response instanceof IResponse))
                $Response = new Response(true, "API executed successfully", $Response);
        } catch (ResponseException $ex) {
            $Response = $ex;
        } catch (\Exception $ex) {
            $Response = new ResponseException($ex->getMessage(), null, $ex);
        }
        return $Response;
    }

    public function setRoute(IRoute $Route) {
        $this->mRoute = $Route;
    }

    /**
     * Get the route for this API
     * @return IRoute
     */
    function getRoute() {
        return $this->mRoute;
    }

    /**
     * Sends headers, executes the request, and renders an IResponse as HTML
     * @param array $request the request to execute
     * @return void
     */
    public function renderHTML(Array $request) {
        if(!headers_sent() && !Util::isCLI())
            header("Content-Type: text/html");
        $Render = new APIInfo();
        //$Response = $this->executeAsResponse($request);
        //$Response->sendHeaders();
        $Render->renderAPI($this);
    }

    /**
     * Sends headers, executes the request, and renders an IResponse as JSON
     * @param array $request the request to execute
     * @return void
     */
    public function renderJSON(Array $request) {
        if(!headers_sent() && !Util::isCLI())
            header("Content-Type: application/json");
        $Response = $this->executeAsResponse($request);
        $Response->sendHeaders();
        $JSON = Util::toJSON($Response);
        echo json_encode($JSON);
    }

    /**
     * Sends headers, executes the request, and renders an IResponse as XML
     * @param array $request the request to execute
     * @return void
     */
    public function renderXML(Array $request) {
        if(!headers_sent() && !Util::isCLI())
            header("Content-Type: text/xml");
        $Response = $this->executeAsResponse($request);
        $Response->sendHeaders();
        $XML = Util::toXML($Response);
        echo $XML->asXML();
    }

    /**
     * Sends headers, executes the request, and renders an IResponse as Plain Text
     * @param array $request the request to execute
     * @return void
     */
    public function renderText(Array $request) {
        $Response = $this->executeAsResponse($request);
        $Response->sendHeaders('text/plain');
        echo $Response."\n";
    }

    public function renderDefault(Array $request) {
        $this->renderText($request);
    }

    /**
     * Add an API Field.
     * @param $name string name of the Field
     * @param IAPIField $Field Describes the Field. Implement IAPIField for custom validation
     * @return $this Return the class instance
     */
    public function addField($name, IAPIField $Field) {
        $this->mFields[$name] = $Field;
        return $this;
    }

    /**
     * Add an array of API Fields
     * @param array $fields associative array of Fields.
     * The array key represents the Field name.
     * @return $this return the class instance
     */
    public function addFields(Array $fields) {
        foreach($fields as $name => $Field)
            $this->mFields[$name] = $Field;
        return $this;
    }

    /**
     * Process a request. Validates each Field. Provides optional Field formatting
     * @param array $request the entire web request
     * @return array the processed and validated request data
     * @throws ValidationExceptions if one or more Fields fail to validate
     */
    public function processRequest(Array $request) {
        if($this->mRoute && $arg = $this->mRoute->getNextArg()) {
            foreach($this->mFields as $name=>$Field) {
                if($Field instanceof IAPIParam) {
                    $request[$name] = $arg;
                    if(!$arg = $this->mRoute->getNextArg())
                        break;
                }
            }
        }
        $values = array();
        $FieldExceptions = new ValidationExceptions();
        foreach($this->mFields as $name=>$Field)
        {
            try {
                $value = isset($request[$name]) ? $request[$name] : NULL;
                $values[$name] = $Field->validate($value);
            } catch (ValidationException $ex) {
                $FieldExceptions->addFieldException($name, $ex);
            }
        }

        if(count($FieldExceptions))
            throw $FieldExceptions;
        return $values;
    }

    /**
     * Render this API Call. The output format is based on the requested mimeType from the browser
     * @param IRoute $Route the selected route including remaining arguments
     */
    public function render(IRoute $Route) {
        $this->mRoute = $Route;
        // Parse the request
        $request = $Route->getRequest();

        foreach(Util::getAcceptedTypes() as $mimeType) {
            switch($mimeType) {
                case 'application/json':
                    $this->renderJSON($request);
                    return;
                case 'text/xml':
                    $this->renderXML($request);
                    return;
                case 'text/html':
                    $this->renderHTML($request);
                    return;
                case 'text/plain':
                    $this->renderText($request);
                    return;
            }
        }
        $this->renderDefault($request);
    }

    public function getFields() {
        return $this->mFields;
    }

    /**
     * Returns an array of all routes for this class
     * @param IRouteBuilder $Builder the IRouteBuilder instance
     * @return IRoute[]
     */
    public function getAllRoutes(IRouteBuilder $Builder) {
        $path = $Builder->getHandlerDefaultPath();
        foreach($Builder->getHandlerMethods() as $method)
            $routes[$method] = new Route($method . ' ' . $path, get_called_class());
        if(!isset($routes['GET']))
            $routes['GET'] = new Route('GET ' . $path, 'CPath\Handlers\Views\APIInfo', get_called_class());
        return $routes;
    }
}
//
//class APIRequest extends ArrayObject {
//
//    private $mRequest;
//    private $mRoute;
//
//    public function __construct(Array $request, IRoute $Route=NULL) {
//        $this->mRequest = $request;
//        $this->mRoute = $Route;
//    }
//
//    public function getRoute() {
//        return $this->mRoute;
//    }
//
//    public function &getData() {
//        return $this->mRequest;
//    }
//}

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
 * Class ValidationException
 * @package CPath
 * Thrown when a Field fails to validate
 */
class ValidationException extends \Exception {
    public function getFieldError($fieldName) {
        return sprintf($this->getMessage(), $fieldName);
    }
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
    public $mDescription;
    public function __construct($description=NULL) {
        $this->mDescription = $description;
    }

    public function getDescription() {
        return $this->mDescription;
    }


    public function validate($value) {
        return $value;
    }
}

/**
 * Class RequiredFieldException
 * @package CPath
 * Throw when a required field is missing
 */
class RequiredFieldException extends ValidationException {
    public function getFieldError($fieldName) { return "Field '{$fieldName}' is required."; }
}

/**
 * Class APIRequiredField
 * @package CPath
 * Represents a 'required' API Field
 */
class APIRequiredField extends APIField {
    public function validate($value) {
        if(!$value && $value !== '0')
            throw new RequiredFieldException();
        return parent::validate($value);
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

class APIFilterField extends APIField {

    protected $mFilter, $mOptions;
    /**
     * @param int $filter
     * @param mixed $options
     * @param String $description
     */
    public function __construct($filter, $options=0, $description=NULL) {
        $this->mFilter = $filter;
        $this->mOptions = $options;
        parent::__construct($description);
    }

    public function validate($value) {
        $value = parent::validate($value);
        return filter_var($value, $this->mFilter, $this->mOptions) ?: NULL;
    }
}
class APIRequiredFilterField extends APIFilterField {

    public function validate($value) {
        if(!$value && $value !== '0')
            throw new RequiredFieldException();
        $value = parent::validate($value);
        if(!$value)
            throw new ValidationException("Field '%s' has an invalid format");
        return $value;
    }
}
