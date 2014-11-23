<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/21/14
 * Time: 3:18 PM
 */
namespace CPath\Request\Web;

use BC\User\Profile\Common\CookieUtil;
use CPath\Render\HTML\HTMLMimeType;
use CPath\Render\JSON\JSONMimeType;
use CPath\Render\Text\TextMimeType;
use CPath\Render\XML\XMLMimeType;
use CPath\Request\Cookie\ICookieRequest;
use CPath\Request\Exceptions\RequestException;
use CPath\Request\MimeType\IRequestedMimeType;
use CPath\Request\MimeType\UnknownMimeType;
use CPath\Request\Request;
use CPath\Request\Session\ISessionRequest;
use CPath\Response\IResponse;

class WebRequest extends Request implements ISessionRequest, ICookieRequest
{
    private $mHeaders = null;

	private $mPrefixPath = null;

    public function __construct($method, $path = null, $parameters = array(), IRequestedMimeType $MimeType=null) {
	    if(!$path)
		    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

	    $root = dirname($_SERVER['SCRIPT_NAME']);
	    if (stripos($path, $root) === 0) {
		    $this->mPrefixPath = substr($path, 0, strlen($root));
		    $path = substr($path, strlen($root));
	    }

        parent::__construct($method, $path, $parameters, $MimeType ?: $this->getHeaderMimeType());

        if(preg_match('/\.(js|css|png|gif|jpg|bmp|ico)/i', $this->getPath(), $matches))
            throw new RequestException("File request was passed to Script: ", IResponse::HTTP_NOT_FOUND);
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

	function getQueryStringValue($paramName) {
		if(isset($_GET[$paramName]))
			return $_GET[$paramName];
		return null;
	}

	/**
	 * Return a request parameter (GET) value
	 * @param String $paramName
	 * @return mixed|null the request parameter value or null if not found
	 */

	function getRequestValue($paramName) {
		return parent::getRequestValue($paramName)
			?: (isset($_GET[$paramName])
			? $_GET[$paramName]
			: null);
	}

	/**
     * Get the requested Mime type(s) for rendering purposes
     * @return \CPath\Request\MimeType\IRequestedMimeType
     */
    function getHeaderMimeType() {
        $accepts = 'text/html';
        if (isset($_SERVER['HTTP_ACCEPT'])) {
            $accepts = $_SERVER['HTTP_ACCEPT'];
        } else if (function_exists('getallheaders')) {
            foreach (getallheaders() as $key => $value)
                if ($key == 'Accept')
                    $accepts = $value;
        }

        $Type = null;
        foreach (array_reverse(explode(',', $accepts)) as $type) {
            list($type) = explode(';', $type, 2);
            $type = trim($type);
            switch (strtolower($type)) {
                case 'application/json':
                case 'application/x-javascript':
                case 'text/javascript':
                case 'text/x-javascript':
                case 'text/x-json':
                    $Type = new JSONMimeType($type, $Type);
                    break;
                case 'application/xml':
                case 'text/xml':
                    $Type = new XMLMimeType($type, $Type);
                    break;
	            case '*/*':
	            case 'text/html':
                case 'application/xhtml+xml':
                    $Type = new HTMLMimeType($type, $Type);
                    break;
                case 'text/plain':
                    $Type = new TextMimeType($type, $Type);
                    break;
                default:
                    $Type = new UnknownMimeType($type, $Type);
            }
        }

        return $Type;
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

    /**
     * Return a referenced array representing the request session
     * @param String|null [optional] $key if set, retrieves &$[Session][$key] instead of &$[Session]
     * @return array
     */
    function &getSession($key = null) {
        if (version_compare(phpversion(), '5.4.0', '>=')) {
            $active = session_status() === PHP_SESSION_ACTIVE ? TRUE : FALSE;
        } else {
            $active = session_id() === '' ? FALSE : TRUE;
        }
        if(!$active)
            session_start();

        if($key === null)
            return $_SESSION;

        if(!isset($_SESSION[$key]))
            $_SESSION[$key] = array();

        return $_SESSION[$key];
    }

	/**
	 * Start a new session
	 * @param bool $reset
	 * @throws \Exception
	 * @return bool true if session was started, otherwise false
	 */
	function startSession($reset=false) {
		if($reset) {
			session_unset();
			session_regenerate_id();
			session_start();
		} else {
			if(isset($_SESSION))
				return true;

			if(headers_sent($file, $line))
				throw new \Exception("Cannot Start Session: Headers already sent by {$file}:{$line}");

			if(!session_start())
				return false;
		}

		return true;
	}

	/**
	 * End current session
	 * @return bool true if session was started, otherwise false
	 */
	function endSession() {
		session_regenerate_id();
		session_destroy();
	}


    /**
     * Get a cookie
     * @param String $name retrieves &$[Cookie][$name]
     * @return String|null
     */
    function getCookie($name) {
        return isset($_COOKIE[$name]) ? $_COOKIE[$name] : null;
    }

    /**
     * Set a cookie
     * @param $name
     * @param string $value
     * @param int $maxage
     * @param string $path
     * @param string $domain
     * @param bool $secure
     * @param bool $HTTPOnly
     * @return bool
     */
    function sendCookie($name, $value = '', $maxage = 0, $path = '', $domain = '', $secure = false, $HTTPOnly = false) {
        $path = $this->getDomainPath(false) . $path;
        $CookieUtil = new CookieUtil();
        return $CookieUtil->sendCookie($name, $value, $maxage, $path, $domain, $secure, $HTTPOnly);
    }
}