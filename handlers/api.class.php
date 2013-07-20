<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Handlers;
use CPath\Interfaces\IApi;
use CPath\Interfaces\IResponseAggregate;
use CPath\Interfaces\IResponseHelper;
use CPath\Route;
use CPath\Util;
use CPath\Build;
use CPath\Interfaces\IResponse;
use CPath\Interfaces\IHandler;
use CPath\Model\MultiException;
use CPath\Model\Response;
use CPath\Model\ResponseException;
use CPath\Builders\BuildRoutes;
use CPath\Handlers\Api\View\ApiInfo;

/**
 * Class ApiHandler
 * @package CPath
 *
 * Provides a Handler template for API calls
 */
abstract class Api implements IApi {

    const BUILD_IGNORE = false;     // API Calls are built to provide routes

    const ROUTE_METHODS = 'GET|POST|CLI';     // Default accepted methods are GET and POST
    const ROUTE_PATH = NULL;        // No custom route path. Path is based on namespace + class name

    /** @var IApiField[] */
    private $mFields = array();
    private $mRoutes=NULL;     // API Fields


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

    /**
     * Sends headers and renders an IResponse as HTML
     * @param IResponse $Response
     * @return void
     */
    public function renderHTML(IResponse $Response) {
        $Response->sendHeaders('text/html');
        $Render = new ApiInfo();
        $Render->render($this, $Response);
    }

    /**
     * Sends headers and renders an IResponse as JSON
     * @param IResponse $Response
     * @return void
     */
    public function renderJSON(IResponse $Response) {
        $Response->sendHeaders('application/json');
        $JSON = Util::toJSON($Response);
        echo json_encode($JSON);
    }

    /**
     * Sends headers and renders an IResponse as XML
     * @param IResponse $Response
     * @return void
     */
    public function renderXML(IResponse $Response) {
        $Response->sendHeaders('text/xml');
        $XML = Util::toXML($Response);
        echo $XML->asXML();
    }

    /**
     * Sends headers and renders an IResponse as Plain Text
     * @param IResponse $Response
     * @return void
     */
    public function renderText(IResponse $Response) {
        $Response->sendHeaders('text/plain');
        echo $Response."\n";
    }

    /**
     * Add an API Field.
     * @param $name string name of the Field
     * @param IApiField $Field Describes the Field. Implement IApiField for custom validation
     * @return $this Return the class instance
     */
    public function addField($name, IApiField $Field) {
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
     * @param array $args an array of arguments parsed out of the route path
     */
    public function render(Array $args) {

        // Parse the request
        if(!$_POST && in_array(Util::getUrl('method'), array('GET', 'CLI'))) {                // if GET
            $request = $_GET;
        } else {                                                                        // else POST
            if(!$_POST && Util::getHeader('Content-Type') === 'application/json') {     // if JSON Object,
                $request = json_decode( file_get_contents('php://input'), true);        // Parse out json
            } else {
                $request = $_POST;                                                      // else use POST
            }
        }

        if($args) {
            $i = 0;
            foreach($this->mFields as $name=>$Field) {
                if(!isset($args[$i]))
                    break;
                if(!empty($args[$i]) && $Field instanceof IApiParam) {
                    $request[$name] = $args[$i];
                    if(!isset($args[$i]))
                        break;
                }
                $i++;
            }
        }
        $Response = $this->executeAsResponse($request);

        foreach(Util::getAcceptedTypes() as $mimeType) {
            switch($mimeType) {
                case 'application/json':
                    $this->renderJSON($Response);
                    return;
                case 'text/xml':
                    $this->renderXML($Response);
                    return;
                case 'text/html':
                    $this->renderHTML($Response);
                    return;
                case 'text/plain':
                    $this->renderText($Response);
                    return;
            }
        }
    }

    public function getFields() {
        return $this->mFields;
    }

    public function getDisplayRoute(&$methods, $route=NULL) {
        $methods = array('GET', 'POST');
        if(!$route) $route = Route::getCurrentRoute()->getRoute();
        foreach($this->mFields as $name => $Field) {
            if(!($Field instanceof IApiParam))
                continue;
            $route .= '/:' . $name ;
        }
        return $route;
    }

}

/**
 * Class IApiField
 * @package CPath
 * Represents an API Field
 */
interface IApiField {
    public function validate($value);
    public function getDescription();
}

/**
 * Class ApiParam
 * @package CPath
 * This interface tags an API Field as a route parameter.
 */
interface IApiParam extends IApiField {

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
        parent::add($ex->getFieldError($fieldName));
    }
}

/**
 * Class ApiField
 * @package CPath
 * Represents an 'optional' API Field
 */
class ApiField implements IApiField {
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
 * Class ApiRequiredField
 * @package CPath
 * Represents a 'required' API Field
 */
class ApiRequiredField extends ApiField {
    public function validate($value) {
        if(!$value && $value !== '0')
            throw new RequiredFieldException();
        return parent::validate($value);
    }
}

/**
 * Class ApiParam
 * @package CPath
 * Represents a Parameter from the route path
 */
class ApiParam extends ApiRequiredField implements IApiParam {
}

class ApiFilterField extends ApiField {

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
class ApiRequiredFilterField extends ApiFilterField {

    public function validate($value) {
        if(!$value && $value !== '0')
            throw new RequiredFieldException();
        $value = parent::validate($value);
        if(!$value)
            throw new ValidationException("Field '%s' has an invalid format");
        return $value;
    }
}