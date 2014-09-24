<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/21/14
 * Time: 3:18 PM
 */
namespace CPath\Request\Web;

use CPath\Render\HTML\HTMLMimeType;
use CPath\Render\JSON\JSONMimeType;
use CPath\Render\Text\TextMimeType;
use CPath\Render\XML\XMLMimeType;
use CPath\Request\MimeType\IRequestedMimeType;
use CPath\Request\MimeType\UnknownMimeType;
use CPath\Request\Request;

class WebRequest extends Request
{
    /** @var IRequestedMimeType */
    private $mMimeType = null;
    private $mHeaders = null;

    private $mMethodName;

    public function __construct($method, $path = null, Array $params = array()) {
        $this->mMethodName = $method;
        if (!$path)
            $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        parent::__construct($path, $params);
    }

    /**
     * Get the Request Method (POST, PUT, PATCH, DELETE, or CLI)
     * @return String
     */
    function getMethodName() {
        return $this->mMethodName;
    }

    /**
     * Checks a request value to see if it exists
     * @param string $paramName the parameter name
     * @return bool
     */
    function hasValue($paramName) {
        if(parent::hasValue($paramName))
            return true;

        $values = $this->getAllValues();
        if(!empty($values[$paramName]))
            return true;

        return false;
    }

    function getAllValues() {
        return $_GET;
    }

    /**
     * Set the requested Mime type for this request
     * @param IRequestedMimeType $MimeType
     * @return void
     */
    function setMimeType(IRequestedMimeType $MimeType) {
        $this->mMimeType = $MimeType;
    }

    /**
     * Get the requested Mime type(s) for rendering purposes
     * @return \CPath\Request\MimeType\IRequestedMimeType
     */
    function getMimeType() {
        if ($this->mMimeType)
            return $this->mMimeType;

        $accepts = 'text/html';
        if (isset($_SERVER['HTTP_ACCEPT'])) {
            $accepts = $_SERVER['HTTP_ACCEPT'];
        } else if (function_exists('getallheaders')) {
            foreach (getallheaders() as $key => $value)
                if ($key == 'Accept')
                    $accepts = $value;
        }

        foreach (explode(',', $accepts) as $type) {
            list($type) = explode(';', $type, 2);
            $type = trim($type);
            switch (strtolower($type)) {
                case 'application/json':
                case 'application/x-javascript':
                case 'text/javascript':
                case 'text/x-javascript':
                case 'text/x-json':
                    $this->mMimeType = new JSONMimeType($type, $this->mMimeType);
                    break;
                case 'application/xml':
                case 'text/xml':
                $this->mMimeType = new XMLMimeType($type, $this->mMimeType);
                    break;
                case 'text/html':
                case 'application/xhtml+xml':
                    $this->mMimeType = new HTMLMimeType($type, $this->mMimeType);
                    break;
                case 'text/plain':
                    $this->mMimeType = new TextMimeType($type, $this->mMimeType);
                    break;
                default:
                    $this->mMimeType = new UnknownMimeType($type, $this->mMimeType);
            }
        }

        return $this->mMimeType;
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
        $headers = self::getAllHeaders();
        return $headers[$name];
    }

}