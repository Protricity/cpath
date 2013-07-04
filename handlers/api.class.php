<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Handlers;
use CPath\Interfaces\IResponseHelper;
use CPath\Util;
use CPath\Build;
use CPath\Interfaces\IResponse;
use CPath\Interfaces\IHandler;
use CPath\Models\MultiException;
use CPath\Models\Response;
use CPath\Models\ResponseException;
use CPath\Builders\BuildRoutes;
use CPath\Handlers\Api\View\ApiInfo;

/**
 * Class ApiHandler
 * @package CPath
 *
 * Provides a Handler template for API calls
 */
abstract class Api implements IHandler {

    const BUILD_IGNORE = false;     // API Calls are built to provide routes

    const ROUTE_METHODS = 'GET|POST';     // Default accepted methods are GET and POST
    const ROUTE_PATH = NULL;        // No custom route path. Path is based on namespace + class name

    /** @var IApiField[] */
    private $mFields = array(), $mRoutes=NULL;     // API Fields

    /**
     * Execute this API Endpoint with the entire request.
     * This method must call processRequest to validate and process the request object.
     * @param array $request associative array of request Fields, usually $_GET or $_POST
     * @return \CPath\Interfaces\IResponse the api call response with data, message, and status
     */
    abstract function execute(Array $request);

    /**
     * Add an API Field.
     * @param $name string name of the Field
     * @param IApiField $Field Describes the Field. Implement IApiField for custom validation
     * @return $this Return the class instance
     */
    protected function addField($name, IApiField $Field) {
        $this->mFields[$name] = $Field;
        return $this;
    }

    /**
     * Add an array of API Fields
     * @param array $fields associative array of Fields.
     * The array key represents the Field name.
     * @return $this return the class instance
     */
    protected function addFields(Array $fields) {
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
        if(!$_POST && strcasecmp(Util::getUrl('method'), 'get') === 0) {                // if GET
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
                if($Field instanceof IApiParam) {
                    $request[$name] = $args[$i++];
                    if(!isset($args[$i]))
                        break;
                }
            }
        }
        try {
            $Response = $this->execute($request);
            if(!($Response instanceof IResponse))
                $Response = new Response(true, "API executed successfully", $Response);
        } catch (ResponseException $ex) {
            $Response = $ex;
        } catch (\Exception $ex) {
            $Response = new ResponseException($ex->getMessage(), null, $ex);
        }

        foreach(Util::getAcceptedTypes() as $mimeType) {
            switch($mimeType) {
                case 'application/json':
                    $Response->sendHeaders($mimeType);
                    $JSON = Util::toJSON($Response);
                    echo json_encode($JSON);
                    return;
                case 'text/xml':
                    $Response->sendHeaders($mimeType);
                    $XML = Util::toXML($Response);
                    echo $XML->asXML();
                    return;
                case 'text/html':
                    $Response->sendHeaders($mimeType);
                    $Render = new ApiInfo();
                    $Render->render($this, $Response);
                    return;
                case 'text/plain':
                    $Response->sendHeaders($mimeType);
                    echo $Response."\n";
                    return;
            }
        }
    }

    public function getFields() {
        return $this->mFields;
    }
    /**
     * Provides the formatted route for viewing purposes
     * @return array the formatted route(s)
     */
    public function getDisplayRoutes() {
        if(!$this->mRoutes) {
            $this->mRoutes = array();
            foreach(BuildRoutes::getHandlerRoutes(new \ReflectionClass($this)) as $route) {
                foreach($this->mFields as $name => $Field) {
                    if(!($Field instanceof IApiParam))
                        continue;
                    $route .= '/:' . $name ;
                }
                $this->mRoutes [] = $route;
            }
        }
        return $this->mRoutes;
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
