<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Request;

use CPath\Interfaces\ILogEntry;
use CPath\Interfaces\ILogListener;
use CPath\Interfaces\IRequest;
use CPath\Interfaces\IRoute;
use CPath\Interfaces\IShortOptions;
use CPath\Log;
use CPath\LogException;
use CPath\Model\ArrayObject;
use CPath\Model\FileUpload;
use CPath\Model\MissingRoute;
use CPath\Router;

class CLI extends ArrayObject implements ILogListener, IRequest, IShortOptions {

    private
        $mMethod,
        $mPath,
        $mHeaders = array(),
        $mArgs = array(),
        $mPos = 0,
        $mRequest = array(),
        $mShortRequests = array();
    /** @var IRoute */
    private
        $mRoute = NULL;

    protected function __construct(Array $args) {

        if(!$args[0]) {
            $this->mMethod = 'CLI';
        } else {
            if(preg_match('/^('.str_replace(',', '|', IRoute::METHODS).')(?: (.*))?$/i', $args[0], $matches)) {
                array_shift($args);
                $this->mMethod = strtoupper($matches[1]);
                if(!empty($matches[2]))
                    array_unshift($args, $matches[2]);
            } else {
                $this->mMethod = 'CLI';
            }
        }

        $args2 = array();
        for($i=0; $i<sizeof($args); $i++) {
            if(is_array($args[$i])) {
                $this->mRequest = $args[$i] + $this->mRequest;
                continue;
            }
            $arg = trim($args[$i]);
            if($arg === '' || $arg === '-')
                continue;
            if($arg[0] == '-') {
                $val = true;
                if(!empty($args[$i+1]) && $args[$i+1][0] !== '-')
                    $val = $args[++$i];

                if($arg[1] == '-')
                    $this->mShortRequests[substr($arg, 2)] = $val;
                else
                    $this->mRequest[substr($arg, 1)] = $val;
            } else {
                $args2[] = $arg;
            }
        }
        $args = $args2;
        if($args) {
            if($args[0])
                foreach(array_reverse(explode('/', array_shift($args))) as $a)
                    if($a) array_unshift($args, $a);
            $parse = parse_url('/'.implode('/', $args));
            if(isset($parse['query'])) {
                parse_str($parse['query'], $query);
                $this->mRequest = $query + $this->mRequest;
            }
            $this->mPath = $parse['path'];
            //$this->mArgs = $args;
        } else {
            $this->mPath = '/';
            //$this->mArgs = array();
        }
    }

    public function setOutputLog($enable=true) {
        $enable ? Log::addCallback($this) : Log::removeCallback($this);
    }

    // Implement IRequest

    /**
     * Get the Request Method (GET, POST, PUT, PATCH, DELETE, or CLI)
     * @return String the method
     */
    function getMethod() { return $this->mMethod; }

    /**
     * Get the URL Path
     * @return String the url path starting with '/'
     */
    function getPath() { return $this->mPath; }

    /**
     * Returns Request headers
     * @param String|Null $key the header key to return or all headers if null
     * @return mixed
     */
    function getHeaders($key=NULL) {
        if($key === NULL)
            return $this->mHeaders;
        return isset($this->mHeaders[$key]) ? $this->mHeaders[$key] : NULL;
    }

//    /**
//     * Add an argument to the arg list
//     * @param String $arg the argument value toa dd
//     * @return void
//     */
//    function addArg($arg) {
//        $this->mArgs[] = $arg;
//    }

    /**
     * Return the next argument for this request
     * @return String argument
     */
    function getNextArg() {
        return isset($this->mArgs[$this->mPos])
            ? $this->mArgs[$this->mPos++]
            : NULL;
    }

    /**
     * Get the IRoute instance for this request
     * @return IRoute
     */
    function getRoute() {
        return $this->mRoute;
    }

//    /**
//     * Set the IRoute instance for this request
//     * @param IRoute $Route
//     * @return void
//     */
//    function setRoute(IRoute $Route) {
//        $this->mArgs = $Route->getRequestArgs($this);
//        $this->mRoute = $Route;
//    }

    /**
     * Merges an associative array into the current request
     * @param array $request the array to merge
     * @param boolean $replace if true, the array is replaced instead of merged
     * @return void
     */
    function merge(Array $request, $replace=false) {
        if($replace) $this->mRequest = $request;
        $this->mRequest = $request + $this->mRequest;
    }

    /**
     * Attempt to find a Route
     * @return IRoute the route instance found. MissingRoute is returned if no route was found
     */
    public function findRoute() {
        $routePath = $this->mMethod . ' ' . $this->mPath;
        $Route = Router::findRoute($routePath, $args)
            ?: new MissingRoute($routePath);
        $this->mRoute = $Route;
        $this->mArgs = $args;
        return $Route;
    }

    // Implement ILogListner

    function onLog(ILogEntry $log)
    {
        echo $log->getMessage(),"\n";
        if($log instanceof LogException)
            echo $log->getException();
    }

    // Extend ArrayObject

    /**
     * Return a reference to this object's associative array
     * @return array the associative array
     */
    protected function &getArray() {
        return $this->mRequest;
    }

    /**
     * Returns a list of mimetypes accepted by this request
     * @return Array
     */
    function getMimeTypes() {
        return array('text/plain');
    }

    // Implement IShortOptions


    /**
     * Add or generate a short option for the list
     * @param String $fieldName the field name
     * @param String $shortName the short name representing the field name
     */
    function processShortOption($fieldName, $shortName) {
        if(isset($this->mShortRequests[$shortName]))
            $this->mRequest[$fieldName] = $this->mShortRequests[$shortName];
    }

    /**
     * Prevent notices and return null if the parameter is missing
     * @param mixed $offset
     * @return mixed|NULL .
     */
    public function offsetGet($offset) {
        return isset($this->mRequest[$offset]) ? $this->mRequest[$offset] : NULL;
    }

    // Statics

    static function fromArgs($args, Array $request=NULL) {
        if(is_string($args))
            $args = explode(' ', $args);
        $CLI = new CLI($args);
        if($request)
            $CLI->merge($request);
        return $CLI;
    }

    static function fromRequest($force=false, $log=true) {
        static $CLI = NULL;
        if($CLI && !$force)
            return $CLI;
        $args = $_SERVER['argv'];
        array_shift($args);
        $CLI = new CLI($args);
        if($log)
            $CLI->setOutputLog(true);
        $CLI->mHeaders = function_exists('getallheaders')
            ? getallheaders()
            : array();
        return $CLI;
    }

    /**
     * Returns a file upload by name, or throw an exception
     * @param mixed|NULL $_path optional varargs specifying a path to data
     * Example: ->getFileUpload(0, 'key') gets $_FILES[0]['key'] formatted as a FileUpload instance;
     * @return FileUpload
     * @throws \InvalidArgumentException if the file was not found
     */
    function getFileUpload($_path=NULL) {
        throw new \InvalidArgumentException("File upload is not supported in CLI requests");
    }
}