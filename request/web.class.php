<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Request;

use CPath\Base;
use CPath\Config;
use CPath\Interfaces\ILogEntry;
use CPath\Interfaces\ILogListener;
use CPath\Interfaces\IRequest;
use CPath\Interfaces\IRoute;
use CPath\Log;
use CPath\LogException;
use CPath\Model\ArrayObject;
use CPath\Model\FileRequestRoute;
use CPath\Model\FileUpload;
use CPath\Model\MissingRoute;
use CPath\Router;
use CPath\RouterAPC;

class Web extends ArrayObject implements IRequest {

    private
        $mMethod,
        $mPath,
        $mTypes,
        $mHeaders = array(),
        $mArgs = array(),
        $mPos = 0,
        $mUploads = null,
        $mRequest = array();

    /** @var IRoute */
    private
        $mRoute = NULL;

    protected function __construct() {}

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

    /**
     * Returns a list of mimetypes accepted by this request
     * @return Array
     */
    function getMimeTypes() {
        return $this->mTypes;
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
        $args = array();
        $routePath = $this->mMethod . ' ' . $this->mPath;
        if(($ext = pathinfo($routePath, PATHINFO_EXTENSION))
            && in_array(strtolower($ext), array('js', 'css', 'png', 'gif', 'jpg', 'bmp', 'ico'))) {
            $Route = new FileRequestRoute($routePath);
        } elseif(Config::$APCEnabled) {
            $Route = RouterAPC::findRoute($routePath, $args);
        } else {
            $Route = Router::findRoute($routePath, $args);
        }
        if(!$Route)
            $Route = new MissingRoute($routePath);
        $this->mRoute = $Route;
        $this->mArgs = $args;
        return $Route;
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
     * Prevent notices and return null if the parameter is missing
     * @param mixed $offset
     * @return mixed|NULL .
     */
    public function offsetGet($offset) {
        return isset($this->mRequest[$offset]) ? $this->mRequest[$offset] : NULL;
    }

    // Static

    /**
     * Return an instance of Web from the current request
     * @return Web
     */
    static function fromRequest() {
        static $Web = NULL;
        if($Web) return $Web;
        $Web = new Web();

        $parse = parse_url($_SERVER['REQUEST_URI']);
        $Web->mMethod = isset($_SERVER["REQUEST_METHOD"]) ? strtoupper($_SERVER["REQUEST_METHOD"]) : 'GET';
        if($Web->mMethod == 'CLI' && !Config::$AllowCLIRequest)
            throw new \Exception("Web requests for CLI are disabled");

        $root = dirname($_SERVER['SCRIPT_NAME']);
        $path = $parse["path"];
        if(stripos($path, $root) === 0)
            $path = substr($path, strlen($root));
        $Web->mPath = $path;

        if(function_exists('getallheaders')) {
            $Web->mHeaders = getallheaders();
        } else {
            $headers = array();
            foreach ($_SERVER as $name => $value)
                if (substr($name, 0, 5) == 'HTTP_')
                    $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
            $Web->mHeaders = $headers;
        }

        $types = array();
        foreach(explode(',', strtolower($Web->getHeaders('Accept'))) as $type) {
            list($type) = explode(';', $type, 2);
            switch ($type = trim($type)) {
                case 'application/json':
                case 'application/x-javascript':
                case 'text/javascript':
                case 'text/x-javascript':
                case 'text/x-json':
                    $types['application/json'] = true;
                    break;
                case 'application/xml':
                case 'text/xml':
                    $types['application/xml'] = true;
                    break;
                case 'text/html':
                case 'application/xhtml+xml':
                    $types['text/html'] = true;
                    break;
                case 'text/plain':
                    $types['text/plain'] = true;
                    break;
                default:
                    $types[$type] = true;
            }
        }
        $Web->mTypes = array_keys($types);

        if($_POST)
            $Web->mRequest = $_POST;
        switch($Web->mMethod) {
            case 'GET':
                $Web->mRequest = $_GET;
                break;
            case 'POST':
            case 'PUT':
            case 'PATCH':
            case 'DELETE':
                $input = file_get_contents('php://input');
                if($Web->getHeaders('Content-Type') === 'application/json') {
                    $Web->mRequest = json_decode($input , true);
                } else {
                    parse_str($input, $request);
                    $Web->mRequest = $request;
                }
                break;
            default:
                Log::e(__CLASS__, "Invalid Request Method: " . $Web->mMethod);
                $Web->mRequest = array();
        }

        return $Web;
    }

    /**
     * Returns a file upload by name, or throw an exception
     * @param mixed|NULL $_path optional varargs specifying a path to data
     * Example: ->getFileUpload(0, 'key') gets $_FILES[0]['key'] formatted as a FileUpload instance;
     * @return FileUpload
     * @throws \InvalidArgumentException if the file was not found
     */
    function getFileUpload($_path=NULL) {
        if($this->mUploads === null)
            $this->mUploads = FileUpload::getAll();
        if($_path === NULL)
            return $this->mUploads;
        $data =& $this->mUploads;
        foreach(func_get_args() as $arg) {
            if(!is_array($data) || !isset($data[$arg]))
                throw new \InvalidArgumentException("Invalid file upload path at '{$arg}': " . implode('.', func_get_args()));
            $data = &$data[$arg];
        }
        return $data;
    }
}