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
use CPath\Request\Parameter\FormField;
use CPath\Request\Parameter\IRequestParameter;
use CPath\Request\Parameter\Parameter;
use CPath\Request\Web\CLIWebRequest;
use CPath\Request\Web\WebFormRequest;
use CPath\Request\Web\WebRequest;

class Request implements IRequest
{
	private $mArgPos = 0;
	private $mArgs;
    /** @var ILogListener[] */
    private $mListeners=array();

    private $mMethodName;
    /** @var IRequestedMimeType */
    private $mMimeType=null;

	/** @var IRequestParameter[] */
    private $mParams = array();
//    private $mMap = null;

    public function __construct($method, $path, $args = array(), IRequestedMimeType $MimeType=null) {
        $this->mMethodName = $method;
        $this->mPath = $path ? '/' . ltrim($path, '/') : '/';
        $this->mArgs = $args;
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


	protected function addParam(IRequestParameter $Parameter) {
		$this->mParams[$Parameter->getName()] = $Parameter;
	}

	/**
	 * Return all request parameters collected by ::getValue
	 * @return IRequestParameter[]
	 */
	function getParameters() {
		return $this->mParams;
	}


	/**
	 * Return a request parameter (GET) value
	 * @param String $paramName
	 * @return mixed|null the request parameter value or null if not found
	 */
	function getRequestValue($paramName) {
		return null;
	}

	/**
	 * Return a request value
	 * @param String|IRequestParameter $Parameter string or instance
	 * @param null $description
	 * @internal param null|String $description
	 * @return mixed the validated parameter value
	 */
	function getValue($Parameter, $description = null) {
		if(!$Parameter instanceof IRequestParameter) {
			$name = $Parameter;
			$Parameter = new FormField($name, $description);
			if($description || !isset($this->mParams[$name]))
				$this->mParams[$name] = $Parameter;

		} else {
			$this->mParams[$Parameter->getName()] = $Parameter;
		}
		return $Parameter->validateRequest($this);
	}

	function hasArgumentValue($argIndex) {
		return isset($this->mArgs[$argIndex]);
	}

	/**
	 * Get the next argument value or null if no more arguments are found
	 * @param null $index if set, returns the value at index, otherwise the next value
	 * @param bool $reset if set resets the current position to $index ?: 0
	 * @return mixed|null the argument value or null if not found
	 */
	function getArgumentValue($index=null, $reset=false) {
		if($index !== null) {
			if($reset && is_int($index))
				$this->mArgPos = $index;
			return isset($this->mArgs[$index]) ? $this->mArgs[$index] : null;
		}

		if($argIndex = $this->mArgs[$this->mArgPos])
			return $this->mArgs[$this->mArgPos++];

		return null;
	}

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
                    $this->mArgs[substr($routeArg, 1)] = $requestPathArg;

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
//
//    /**
//     * Set the request parameters expected by this request
//     * @param IParameterMap $Map
//     */
//    function setRequestParameters(IParameterMap $Map) {
//        $this->mMap = $Map;
//    }
//
//    /**
//     * Map request parameters for this object
//     * @param IParameterMapper $Map
//     * @return void
//     */
//    function mapParameters(IParameterMapper $Map) {
//        // TODO: merge?
//        if($this->mMap) {
//            $this->mMap->mapParameters($Map);
//        } else {
//            foreach($this->mParams as $name => $data) {
//                if(isset($data[1]) && ($data[1] & IRequest::PARAM_REQUIRED))
//                    $Map->map(new RequiredParameter($name, $data[0]));
//                else
//                    $Map->map(new Parameter($name, $data[0]));
//            }
//        }
//    }

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