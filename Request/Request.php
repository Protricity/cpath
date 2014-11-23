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

class Request implements IRequest
{
	private $mParameters;
    /** @var ILogListener[] */
    private $mListeners=array();

    private $mMethodName;
    /** @var IRequestedMimeType */
    private $mMimeType=null;

    public function __construct($method, $path, $parameters = array(), IRequestedMimeType $MimeType=null) {
        $this->mMethodName = $method;
        $this->mPath = $path ? '/' . ltrim($path, '/') : '/';
        $this->mParameters = $parameters;
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

	/**
	 * Return the route path for this request
	 * @return String the route path starting with '/'
	 */
	function getPath() {
		return $this->mPath;
	}


	/**
	 * @param $paramName
	 * @return String|null
	 */
	function getRequestValue($paramName) {
		return isset($this->mParameters[$paramName])
			? $this->mParameters[$paramName]
			: null;
	}

    /**
     * Matches a route prefix to this request and updates the method args with any extra path
     * @param $routePrefix '[method] [path]'
     * @return bool true if the route matched
     */
    function match($routePrefix) {
	    if(strpos($routePrefix, ' ') !== false)
            list($routeMethod, $path) = explode(' ', $routePrefix, 2);
	    else
		    list($routeMethod, $path) = array('ANY', $routePrefix);

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
                    $this->mParameters[substr($routeArg, 1)] = $requestPathArg;

                } elseif(strcasecmp($routeArg, $requestPathArg) !== 0) {
                    return false;

                }
            }

            if(isset($routeArgs[$i])) // TODO: extra route return false?
                return false;

        } else {
            if(strpos($path, '*') !== false) {
                $path = preg_quote($path, '/');
                $pattern = str_replace( '\*' , '.*?', $path);
                if(!preg_match( '/^' . $pattern . '$/i', $this->getPath()))
                    return false;
            } elseif (strcasecmp(rtrim($this->getPath(), '/'), rtrim($path, '/')) !== 0) {
                return false;
            }

        }

//        $this->log("Matched " . $routePrefix);
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
     * Log an exception inst
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
//
//    /**
//     * Returns an associative array of params and their descriptions
//     * @return array
//     */
//    function getParameterDescriptions() {
//        return $this->mParams;
//    }

    /**
     * @param bool $withDomain
     * @return String
     */
    function getDomainPath($withDomain=true) {
        if($withDomain) {
            $protocol = 'http';
            if(isset($_SERVER['HTTPS']))
                $protocol = ($_SERVER['HTTPS'] && $_SERVER['HTTPS'] != "off") ? "https" : "http";

            return $protocol . "://" . $_SERVER['SERVER_NAME'];
        }
        return '';
    }

    // Static

    /**
     * Create a new IRequest inst from environment variables
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

	/**
	 * (PHP 5 &gt;= 5.0.0)<br/>
	 * Whether a offset exists
	 * @link http://php.net/manual/en/arrayaccess.offsetexists.php
	 * @param mixed $offset <p>
	 * An offset to check for.
	 * </p>
	 * @return boolean true on success or false on failure.
	 * </p>
	 * <p>
	 * The return value will be casted to boolean if non-boolean was returned.
	 */
	public function offsetExists($offset) {
//		$Params = new SessionParameters($this);
//		if(!$Params->has($offset)) {
//			$Parameter = new Parameter($offset);
//			$Params->add($Parameter);
//		}
		return $this->getRequestValue($offset) !== null;
	}

	/**
	 * (PHP 5 &gt;= 5.0.0)<br/>
	 * Offset to retrieve
	 * @link http://php.net/manual/en/arrayaccess.offsetget.php
	 * @param mixed $offset <p>
	 * The offset to retrieve.
	 * </p>
	 * @return mixed Can return all value types.
	 */
	public function offsetGet($offset) {
//		$Params = new SessionParameters($this);
//		if(!$Params->has($offset)) {
//			$Parameter = new RequiredParameter($offset);
//			$Params->add($Parameter);
//		}
		return $this->getRequestValue($offset);
	}

	/**
	 * (PHP 5 &gt;= 5.0.0)<br/>
	 * Offset to set
	 * @link http://php.net/manual/en/arrayaccess.offsetset.php
	 * @param mixed $offset <p>
	 * The offset to assign the value to.
	 * </p>
	 * @param mixed $value <p>
	 * The value to set.
	 * </p>
	 * @return void
	 */
	public function offsetSet($offset, $value) {
		$this->mParameters[$offset] = $value;
	}

	/**
	 * (PHP 5 &gt;= 5.0.0)<br/>
	 * Offset to unset
	 * @link http://php.net/manual/en/arrayaccess.offsetunset.php
	 * @param mixed $offset <p>
	 * The offset to unset.
	 * </p>
	 * @return void
	 */
	public function offsetUnset($offset) {
		unset($this->mParameters[$offset]);
	}
}