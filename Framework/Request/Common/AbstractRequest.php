<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Framework\Request\Common;

use CPath\Config;
use CPath\Framework\Data\Wrapper\IWrapper;
use CPath\Framework\Request\Interfaces\IRequest;
use CPath\Interfaces\ILogEntry;
use CPath\LogException;
use CPath\Model\ArrayObject;
use CPath\Model\FileUpload;
use CPath\Model\NoUploadFoundException;

abstract class AbstractRequest extends ArrayObject implements IRequest {

    protected
        $mTab = "\t",
        $mTabCount = 0,
        $mHeaders = array(),
        $mPos = 0,
        $mMethod,
        $mPath,
        $mArgs = array(),
        $mRequest = array();

//    /** @var \CPath\Route\IRoute */
//    protected
//        $mRoute = NULL;

    protected function __construct() {
    }

    /**
     * Returns a file upload by name, or throw an exception
     * @param mixed|NULL $_path optional varargs specifying a path to data
     * Example: ->getFileUpload(0, 'key') gets $_FILES[0]['key'] formatted as a FileUpload instance;
     * @return FileUpload
     * @throws NoUploadFoundException if the file was not found
     */
    abstract function getFileUpload($_path=NULL);


    // Implement IRequest

    /**
     * Get the Request Method (GET, POST, PUT, PATCH, DELETE, or CLI)
     * @return String the method
     */
    function getMethod() { return $this->mMethod; }

    /**
     * Get the Route Path starting at the root path of the framework
     * @return String the url path starting with '/'
     */
    function getPath() { return $this->mPath; }

    /**
     * @return Array
     */
    function getArgs() { return $this->mArgs; }

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
     * @param bool $advance if true, the argument position advances forward 1
     * @return String argument
     */
    function getNextArg($advance=true) {
        if(isset($this->mArgs[$this->mPos])) {
            $value = $this->mArgs[$this->mPos];
            if($advance)
                $this->mPos++;
            return $value;
        }
        return NULL;
    }

//    /**
//     * Get the IRoute instance for this request
//     * @return \CPath\Route\IRoute
//     */
//    function getRoute() {
//        return $this->mRoute;
//    }

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

    /**
     * Prevent notices and return null if the parameter is missing
     * @param mixed $offset
     * @return mixed|NULL .
     */
    public function offsetGet($offset) {
        return isset($this->mRequest[$offset]) ? $this->mRequest[$offset] : NULL;
    }
}