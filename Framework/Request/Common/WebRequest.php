<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Framework\Request\Common;

use CPath\Config;
use CPath\Log;
use CPath\Model\FileUpload;

class WebRequest extends AbstractRequest {

    private
        $mMimeTypes,
        $mRawQueryString = null,
        $mUploads = null;

    protected function __construct() {
        parent::__construct();
    }

    /**
     * Returns a list of mimetypes accepted by this request
     * @return Array
     */
    function getMimeTypes() {
        return $this->mMimeTypes;
    }

    /**
     * Get the URL Path
     * @param bool $absolute return with absolute path
     * @param bool $withQuery return with query string
     * @return string
     */
    function getURL($absolute=false, $withQuery=false) {
        return
            ($absolute ? Config::getDomainPath() : '')
            . substr($this->getPath(), 1)
            . ($withQuery ? '?' . $this->getRawQueryString() : '');
    }
    /**
     * Build a url from the request
     * @param bool $withArgs
     * @param bool $withDomain
     * @return string
     */
    function getRequestURL($withArgs=true, $withDomain=false) {
        $path = $this->getPath();

        if($withArgs)
            foreach($this->mArgs as $arg)
                $path .=  '/' . $arg;

        if($withDomain)
            $path = rtrim(Config::$Domain, '/') . $path;

        return $path;
    }

    function getRawQueryString() {
        if($this->mRawQueryString)
            return $this->mRawQueryString;

        return $this->mRawQueryString = http_build_query($this->mRequest);
    }

//    /**
//     * Attempt to find a Route
//     * @return \CPath\Route\IRoute the route instance found. MissingRoute is returned if no route was found
//     */
//    public function findRoute() {
//        $args = array();
//
//        $routePath = $this->mMethod . ' ' . $this->getPath();
//        //$routePathAny = 'ANY ' . $this->getPath();
//        if(($ext = pathinfo($routePath, PATHINFO_EXTENSION))
//            && in_array(strtolower($ext), array('js', 'css', 'png', 'gif', 'jpg', 'bmp', 'ico'))) {
//            $Route = new FileRequestRoute($routePath);
//        } elseif(Config::$APCEnabled) {
//            $Route = RouterAPC::findRoute($routePath, $args);
//                //?: RouterAPC::findRoute($routePathAny, $args);
//        } else {
//            $Route = Router::findRoute($routePath, $args);
//                //?: Router::findRoute($routePathAny, $args);
//        }
//        if(!$Route)
//            $Route = new MissingRoute($routePath);
//        $this->mRoute = $Route;
//        $this->mArgs = $args;
//        return $Route;
//    }

    /**
     * Returns a file upload by name, or throw an exception
     * @param mixed|NULL $_path optional varargs specifying a path to data
     * Example: ->getFileUpload(0, 'key') gets $_FILES[0]['key'] formatted as a FileUpload instance;
     * @return FileUpload|NULL a file upload instance or null if no file upload was found
     * @throws \Exception if the path was invalid
     */
    function getFileUpload($_path=NULL) {
        if($this->mUploads === null)
            $this->mUploads = FileUpload::getAll();
        if($_path === NULL)
            return $this->mUploads;
        $data =& $this->mUploads;
        foreach(func_get_args() as $arg) {
            if(!is_array($data) || !isset($data[$arg]))
                throw new \Exception("Invalid file upload path at '{$arg}': " . implode('.', func_get_args()));
            $data = &$data[$arg];
        }
        return $data;
    }

    // Static

    /**
     * Return an instance of Web from the current request
     * @return WebRequest
     * @throws \Exception
     */
    static function fromRequest() {
        static $Web = NULL;
        if($Web) return $Web;
        $Web = new WebRequest();

        $parse = parse_url($_SERVER['REQUEST_URI']);
        $Web->mRawQueryString = !empty($parse['query']) ? $parse['query'] : null;

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
        $Web->mMimeTypes = array_keys($types);

        if($_POST)
            $Web->mRequest = $_POST;
        else
            switch($Web->mMethod) {
                case 'GET':
                    $Web->mRequest = $_GET;
                    break;
                case 'POST':
                case 'PUT':
                case 'PATCH':
                case 'DELETE':
                    $input = file_get_contents('php://input');
                    $Web->mRawQueryString = $input;
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
}