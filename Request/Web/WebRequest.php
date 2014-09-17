<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/7/14
 * Time: 12:47 PM
 */
namespace CPath\Request\Web;

use CPath\Request\IRequest;
use CPath\Request\Web\GETMethod;
use CPath\Request\IRequestMethod;
use CPath\Request\Web\POSTMethod;
use CPath\Request\MimeType;

class WebRequest implements IRequest
{
    private $mMimeTypes = null;
    private $mPath = null;
    private $mHeaders = null;
    private $mMethod;
    private $mFields = null;

    public function __construct(IRequestMethod $Method=null) {
        $this->mMethod = $Method;
    }

    /**
     * Get the Request Method Instance (GET, POST, PUT, PATCH, DELETE, or CLI)
     * @return \CPath\Request\IRequestMethod
     */
    function getMethod() {
        if($this->mMethod)
            return $this->mMethod;

        if($_SERVER["REQUEST_METHOD"] === 'GET')
            return $this->mMethod = new GETMethod(explode('/', trim($this->mPath, '/')));

        return $this->mMethod = new POSTMethod($_SERVER["REQUEST_METHOD"], explode('/', trim($this->mPath, '/')));
    }

    /**
     * Prompt for a value from the request.
     * @param string $name the parameter name
     * @param string|null $defaultValue [optional] default value if prompt fails
     * @return mixed the parameter value
     * @throws \CPath\Request\Exceptions\RequestParameterException if a prompt failed to produce a result
     * Example:
     * $name = $Request->prompt('name', 'Please enter your name', 'MyName');  // Gets value for parameter 'name' or returns default string 'MyName'
     */
    function getFieldValue($name, $defaultValue=null) {
        $values = $this->getAllFormFieldValues();
        return !empty($values[$name]) ? $values[$name] : $defaultValue;
    }


    function getAllFormFieldValues() {
        if ($this->mFields !== null)
            return $this->mFields;

        if ($this->getHeader('Content-Type') === 'application/json') {
            $input = file_get_contents('php://input');
            $this->mFields = json_decode($input, true);
            return $this->mFields;
        }

        $this->mFields = $_POST;
        return $this->mFields;
    }

    /**
     * Get the route path
     * @return String the route path starting with '/'
     */
    function getPath() {
        if ($this->mPath)
            return $this->mPath;

        $root = dirname($_SERVER['SCRIPT_NAME']);
        $path = parse_url($_SERVER['REQUEST_URI'], 'path');

        if (stripos($path, $root) === 0)
            $path = substr($path, strlen($root));
        $this->mPath = $path;
        return $this->mPath;
    }

    /**
     * Get the requested Mime types
     * @return \CPath\Request\MimeType\IRequestedMimeType[]
     */
    function getMimeTypes() {
        if ($this->mMimeTypes)
            return $this->mMimeTypes;

        $accepts = 'text/html';
        if (isset($_SERVER['HTTP_ACCEPT'])) {
            $accepts = $_SERVER['HTTP_ACCEPT'];
        } else if (function_exists('getallheaders')) {
            foreach (getallheaders() as $key => $value)
                if ($key == 'Accept')
                    $accepts = $value;
        }

        $types = array();
        foreach (explode(',', $accepts) as $type) {
            list($type) = explode(';', $type, 2);
            $type = trim($type);
            switch (strtolower($type)) {
                case 'application/json':
                case 'application/x-javascript':
                case 'text/javascript':
                case 'text/x-javascript':
                case 'text/x-json':
                    $types[] = new \CPath\Render\JSON\JSONMimeType($type);
                    break;
                case 'application/xml':
                case 'text/xml':
                    $types[] = new \CPath\Render\XML\XMLMimeType($type);
                    break;
                case 'text/html':
                case 'application/xhtml+xml':
                    $types[] = new \CPath\Render\HTML\HTMLMimeType($type);
                    break;
                case 'text/plain':
                    $types[] = new \CPath\Render\Text\TextMimeType($type);
                    break;
                default:
                    $types[] = new MimeType\UnknownMimeType($type);
            }
        }

        $this->mMimeTypes = $types;
        return $this->mMimeTypes;
    }


    function getAllHeaders() {
        if ($this->mHeaders !== null)
            return $this->mHeaders;

        if (function_exists('getallheaders'))
            return $this->mHeaders = getallheaders();

        foreach ($_SERVER as $name => $value) {
            if (in_array(substr($name, 0, 5), array('CONTE', 'HTTP_'))) {
                $name = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))));
                $this->mHeaders[$name] = $value;
            }
        }
        return $this->mHeaders;
    }

    function getHeader($name) {
        $headers = $this->getAllHeaders();
        return $headers[$name];
    }

//
//    /**
//     * @return IRequestMethod
//     */
//    function getMethod()
//    {
//        $methodName = $_SERVER["REQUEST_METHOD"];
//
//        switch ($methodName) {
//            case 'GET':
//                $Method = new GETRequest();
//                break;
//            case 'POST':
//                $Method = new POSTRequest();
//                break;
//            case 'PUT':
//                $Method = new POSTRequest();
//                break;
//            case 'PATCH':
//                $Method = new POSTRequest();
//                break;
//            case 'DELETE':
//                $Method = new POSTRequest();
//                break;
//            case 'CLI':
//                $Method = new CLIRequest();
//                break;
////                $input = file_get_contents('php://input');
////                $Web->mRawQueryString = $input;
////                if ($Web->getHeaders('Content-Type') === 'application/json') {
////                    $Web->mRequest = json_decode($input, true);
////                } else {
////                    parse_str($input, $request);
////                    $Web->mRequest = $request;
////                }
////                break;
//            default:
////                Log::e(__CLASS__, "Invalid Request Method: " . $Web->mMethod);
////                $Web->mRequest = array();
//        }
//    }
}