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

abstract class ApiHandler implements Interfaces\IHandler, Interfaces\IBuilder {

    const BUILD_IGNORE = false;

    const ROUTE_METHOD = 'GET';
    const ROUTE_PATH = NULL;

    private $mFields = array();

    public function __construct() {
    }

    /**
     * @param array $request associative array of request parameters, usually $_GET or $_POST
     * @return \CPath\Interfaces\IResponse the api call response with data, message, and status
     */
    abstract function execute(Array $request);

    protected function addField($name, IApiField $Field) {
        $this->mFields[$name] = $Field;
        return $this;
    }
    protected function addFields(Array $fields) {
        foreach($fields as $name => $Field)
            $this->mFields[$name] = $Field;
        return $this;
    }

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
                    $JSON = Util::toJSON($response);
                    if(Base::isDebug()) $JSON['debug'] = array('log'=>Base::getLog());
                    echo json_encode($JSON, JSON_OBJECT_AS_ARRAY);
                    return;
                case 'text/xml':
                    $response->sendHeaders($mimeType);
                    $XML = Util::toXML($response);
                    if(Base::isDebug()) {
                        $Debug = $XML->addChild('debug');
                        foreach(Base::getLog() as $log)
                            $Debug->addChild('log', $log);
                    }
                    echo $XML->asXML();
                    return;
                case 'text/html':
                    $response->sendHeaders($mimeType);
                    echo "<pre>";
                    echo $response."\n";
                    if(Base::isDebug()) {
                        foreach(Base::getLog() as $log)
                            echo $log."\n";
                    }
                    echo "</pre>";
                    return;
                case 'text/plain':
                    $response->sendHeaders($mimeType);
                    echo $response;
                    if(Base::isDebug()) {
                        foreach(Base::getLog() as $log)
                            echo "$log\n";
                    }
            }
        }
    }

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

    public static function build(\ReflectionClass $Class) {
        BuildRoutes::build($Class);
    }

    public static function buildComplete(\ReflectionClass $Class) {
        BuildRoutes::buildComplete($Class);
    }
}

class ValidationException extends \Exception {
    public function getFieldError($fieldName) {
        return sprintf($this->getMessage(), $fieldName);
    }
}
class ValidationExceptions extends MultiException {
    public function addFieldException($fieldName, ValidationException $ex) {
        parent::add($ex->getFieldError($fieldName));
    }
}
interface IApiField {
    public function validate($value);
}

class ApiOptionalField implements IApiField {
    public $mDescription;
    public function __construct($description=NULL) {
        $this->mDescription = $description;
    }

    public function validate($value) {
        return $value;
    }
}

class RequiredFieldException extends ValidationException {
    public function getFieldError($fieldName) { return "Field '{$fieldName}' is required."; }
}
class ApiRequiredField extends ApiOptionalField {
    public function validate($value) {
        if(!$value && $value !== '0')
            throw new RequiredFieldException();
        return parent::validate($value);
    }
}

class ApiParam extends ApiRequiredField {

}