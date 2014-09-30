<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/21/14
 * Time: 2:16 PM
 */
namespace CPath\Request;

use CPath\Request\Log\ILogListener;
use CPath\Request\MimeType\IRequestedMimeType;
use CPath\Request\Web\CLIWebRequest;
use CPath\Request\Web\WebFormRequest;
use CPath\Request\Web\WebRequest;
use CPath\Response\Exceptions\HTTPRequestException;

class Request implements IRequest
{
    private $mValues;
    /** @var ILogListener[] */
    private $mListeners=array();

    private $mMethodName;
    /** @var IRequestedMimeType */
    private $mMimeType=null;

    private $mDescriptions = array();
    private $mPrefixPath = null;

    public function __construct($method, $path = null, $params = array(), IRequestedMimeType $MimeType=null) {

        $urlPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $root = dirname($_SERVER['SCRIPT_NAME']);


        if (!$path && stripos($urlPath, $root) === 0) {
            $this->mPrefixPath = substr($urlPath, 0, strlen($root));
            $urlPath = substr($urlPath, strlen($root));

            $path = $urlPath;
        }


        $this->mMethodName = $method;
        $this->mPath = $path ? '/' . ltrim($path, '/') : '/';
        $this->mValues = $params;
        $this->mMimeType = $MimeType;
    }

    /**
     * Get the Request Method (POST, PUT, PATCH, DELETE, or CLI)
     * @return String
     */
    function getMethodName() {
        return $this->mMethodName;
    }

    /**
     * Get the requested Mime types for rendering purposes
     * @return \CPath\Request\MimeType\IRequestedMimeType[]
     */
    function getMimeType() {
        return $this->mMimeType;
    }

//    /**
//     * Checks a request value to see if it exists
//     * @param string $paramName the parameter name
//     * @return bool
//     */
//    final function hasValue($paramName) {
//        if(isset($this->mValues[$paramName])){
//            //$this->mParams[$paramName] = new RequestParameter(null, $paramName);
//            return true;
//        }
//
//        $values = $this->getAllValues();
//        if(isset($values[$paramName]))
//            return true;
//
//        return false;
//    }

    protected function getParamValue($paramName) {
        if(!empty($this->mValues[$paramName]))
            return $this->mValues[$paramName];

        return null;
    }

    /**
     * Get a request value by parameter name or throw an exception
     * @param string $paramName the parameter name
     * @param string $description [optional] description for this prompt
     * @param int $flags use ::PARAM_REQUIRED for required fields
     * @throws RequestException
     * @return mixed the parameter value
     */
    final function getValue($paramName, $description = null, $flags=0) {
        if($description)
            $this->mDescriptions[$paramName] = array_slice(func_get_args(), 1); //array($description, $flags);

        if($value = $this->getParamValue($paramName))
            return $value;
//
//        if(!empty($this->mValues[$paramName]))
//            return $this->mValues[$paramName];
//
//        $values = $this->getAllValues();
//        if(!empty($values[$paramName]))
//            return $values[$paramName];
//
//        if($value = $this->getMissingValue($paramName, $description, $flags))
//            return $value;

        if($flags & IRequest::PARAM_REQUIRED) {
            $this->mDescriptions[$paramName][1] |= IRequest::PARAM_ERROR;
            throw new RequestException("Missing parameter: " . $paramName);
        }
        return null;
    }
//
//    /**
//     * Get a request value by parameter name or throw an exception
//     * @param string $paramName the parameter name
//     * @param string $description [optional] description for this prompt
//     * @return mixed the parameter value
//     * @throws RequestException if the value was not found
//     */
//    function requireValue($paramName, $description = null) {
//        if($description)
//            $this->mDescriptions[$paramName] = $description;
//
//        if(!empty($this->mValues[$paramName]))
//            return $this->mValues[$paramName];
//
//        $values = $this->getAllValues();
//        if(!empty($values[$paramName]))
//            return $values[$paramName];
//
//        $err = "Missing parameter: " . $paramName;
//        throw new RequestException($err, $this->mDescriptions);
//    }

//    protected function getAllValues() {
//        return array();
//    }

    /**
     * Return the route path for this request
     * @return String the route path starting with '/'
     */
    function getPath() {
        return $this->mPath;
    }

    /**
     * Matches a route prefix to this request and updates the method args with any extra path
     * @param $routePrefix '[method] [path]'
     * @return bool true if the route matched
     */
    function match($routePrefix) {
        list($routeMethod, $path) = explode(' ', $routePrefix, 2);

        $requestMethod = $this->getMethodName();

//        if($this instanceof ILogListener)
//            $this->log("Matching " . $this->getPath() . " to " . $routePrefix);
        // /user/abc123/
        // /user/:id/
        if ($routeMethod !== 'ANY' && $routeMethod !== $requestMethod)
            return false;

        if(($p = strpos($path, ':')) !== false) {
            $routeArgs = explode('/', trim($path, '/'));
            $i=0;
            foreach(explode('/', trim($this->getPath(), '/')) as $requestPathArg) {
                if(!isset($routeArgs[$i]))
                    return false;

                $routeArg = $routeArgs[$i++];

                if($routeArg[0] == ':') {
                    $this->mValues[substr($routeArg, 1)] = $requestPathArg;

                } elseif(strcasecmp($routeArg, $requestPathArg) !== 0) {
                    return false;

                }
            }

            if(isset($routeArgs[$i])) // TODO: extra route return false?
                return false;

        } else {
            if (strcasecmp($this->getPath(), $path) !== 0)
                return false;

        }

            $this->log("Matched " . $routePrefix);
        return true;
    }


    /**
     * Add a log entry
     * @param String $msg The log message
     * @param int $flags [optional] log flags
     * @return void
     */
    function log($msg, $flags = 0) {
        foreach($this->mListeners as $Log)
            $Log->log($msg, $flags);

        $MimeType = $this->getMimeType();
        if($MimeType instanceof ILogListener)
            $MimeType->log($msg, $flags);
    }

    /**
     * Log an exception instance
     * @param \Exception $ex The log message
     * @param int $flags [optional] log flags
     * @return void
     */
    function logEx(\Exception $ex, $flags = 0) {
        foreach($this->mListeners as $Log)
            $Log->logEx($ex, $flags);

        $MimeType = $this->getMimeType();
        if($MimeType instanceof ILogListener)
            $MimeType->logEx($ex, $flags);
    }

    /**
     * Add a log listener callback
     * @param ILogListener $Listener
     * @return void
     */
    function addLogListener(ILogListener $Listener) {
        $this->mListeners[] = $Listener;
    }

    /**
     * Returns an associative array of params and their descriptions
     * @return array
     */
    function getParameterDescriptions() {
        return $this->mDescriptions;
    }

    /**
     * @param bool $withDomain
     * @return String
     */
    function getDomainPath($withDomain=true) {
        $path = $this->mPrefixPath;
        if($withDomain) {
            $protocol = 'http';
            if(isset($_SERVER['HTTPS']))
                $protocol = ($_SERVER['HTTPS'] && $_SERVER['HTTPS'] != "off") ? "https" : "http";

            $path = $protocol . "://" . $_SERVER['SERVER_NAME'] . $path;
        }
        return $path;
    }

    // Static

    /**
     * Create a new IRequest instance from environment variables
     * @param String $route path string or route ([method] [path])
     * @param array $args
     * @return IRequest
     */
    public static function create($route=null, $args=null) {
        $method = null;
        if(($p = strpos($route, ' ')) !== false)
            if($p <=5)
                list($method, $route) = explode(' ', $route, 2);
        //static $Inst = null;
        //if($Inst) return $Inst;

        if (PHP_SAPI === 'cli') {
            $Inst = new CLI\CLIRequest($route, $args);
        } else {
            if(!$method)
                $method = $_SERVER["REQUEST_METHOD"];
            if ($method === 'GET')
                $Inst = new WebRequest($method, $route, $args);
            elseif ($method === 'CLI')
                $Inst = new CLIWebRequest($route, $args);
            else
                $Inst = new WebFormRequest($method, $route, $args);
        }
        return $Inst;
    }
}