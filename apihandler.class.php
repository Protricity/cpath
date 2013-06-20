<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath;

use CPath\Interfaces\IResponse;
use CPath\Models\MultiException;
use CPath\Models\Response;
use CPath\Models\ResponseException;
use CPath\Builders\BuildRoutes;

/**
 * Class ApiHandler
 * @package CPath
 *
 * Provides a Handler template for API calls
 */
abstract class ApiHandler implements Interfaces\IHandler, Interfaces\IBuilder {

    const BUILD_IGNORE = false;     // API Calls are built to provide routes

    const ROUTE_METHOD = 'GET';     // Default accepted method is GET
    const ROUTE_PATH = NULL;        // No custom route path. Path is based on namespace + class name

    private $mFields = array();     // API Fields

    /**
     * Execute this API Endpoint with the entire request.
     * This method must call processRequest to validate and process the request object.
     * @param array $request associative array of request Fields, usually $_GET or $_POST
     * @return \CPath\Interfaces\IResponse the api call response with data, message, and status
     */
    abstract function execute(Array $request);

    /**
     * Add an API Field.
     * @param $name the name of the Field
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
        $request = strcasecmp(Util::getUrl('method'), 'get') === 0
            ? $_GET
            : $_POST;

        if($args) {
            $i = 0;
            foreach($this->mFields as $name=>$Field) {
                if($Field instanceof ApiParam) {
                    $request[$name] = $args[$i++];
                    if(!isset($args[$i]))
                        break;
                }
            }
        }
        try {
            $response = $this->execute($request);
            if(!($response instanceof IResponse))
                $response = new Response($response, "API executed successfully");
        } catch (ResponseException $ex) {
            $response = $ex;
        } catch (\Exception $ex) {
            $response = new ResponseException($ex->getMessage(), null, $ex);
        }

        foreach(Util::getAcceptedTypes() as $mimeType) {
            switch($mimeType) {
                case 'application/json':
                    $response->sendHeaders($mimeType);
                    $JSON = array();
                    Util::toJSON($response, $JSON);
                    if(Base::isDebug())
                        Util::toJSON(array('debug'=>array('log'=>Log::get())), $JSON);
                    echo json_encode($JSON, JSON_OBJECT_AS_ARRAY);
                    return;
                case 'text/xml':
                    $response->sendHeaders($mimeType);
                    $XML = Util::toXML($response);
                    if(Base::isDebug())
                        Util::toXML(array('debug'=>array('log'=>Log::get())), $XML);
                    echo $XML->asXML();
                    return;
                case 'text/html':
                    $response->sendHeaders($mimeType);
                    echo "<pre>";
                    echo $response."\n";
                    /** @var $log ILog */
                    foreach(Log::get() as $log)
                        echo $log."\n";
                    echo "</pre>";
                    return;
                case 'text/plain':
                    $response->sendHeaders($mimeType);
                    echo $response;
                    /** @var $log ILog */
                    foreach(Log::get() as $log)
                        echo "$log\n";
            }
        }
    }

    /**
     * Provides the formatted route for viewing purposes
     * @return string the formatted route
     */
    public function getDisplayRoute() {
        $route = Build::getHandlerRoute($this);
        foreach($this->mFields as $name => $Field) {
            if(!($Field instanceof ApiParam))
                continue;
            $route .= ':' . $name . '/';
        }
        return $route;
    }

    // Statics

    /** Builds the API Endpoint route */
    public static function build(\ReflectionClass $Class) {
        BuildRoutes::build($Class);
    }

    /** Processes the API Endpoint route into the routes file */
    public static function buildComplete(\ReflectionClass $Class) {
        BuildRoutes::buildComplete($Class);
    }
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
 * Class IApiField
 * @package CPath
 * Represents an API Field
 */
interface IApiField {
    public function validate($value);
}

/**
 * Class ApiOptionalField
 * @package CPath
 * Represents an 'optional' API Field
 */
class ApiOptionalField implements IApiField {
    public $mDescription;
    public function __construct($description=NULL) {
        $this->mDescription = $description;
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
class ApiRequiredField extends ApiOptionalField {
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
class ApiParam extends ApiRequiredField {

}