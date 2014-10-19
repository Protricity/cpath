<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/21/14
 * Time: 11:11 PM
 */
namespace CPath\Request;

use CPath\Request\Exceptions\RequestException;
use CPath\Request\Log\ILogListener;
use CPath\Request\Parameter\IRequestParameter;

abstract class AbstractRequestWrapper implements IRequest
{
    private $mRequest;
    /** @var ILogListener[] */
    private $mLogs=array();

    function __construct(IRequest $Request) {
        $this->mRequest = $Request;
    }

    function getWrappedRequest() {
        return $this->mRequest;
    }

    /**
     * Get the requested Mime types for rendering purposes
     * @return \CPath\Request\MimeType\IRequestedMimeType[]
     */
    function getMimeType() {
        return $this->mRequest->getMimeType();
    }

	/**
	 * Return a request parameter (GET) value
	 * @param $paramName
	 * @return mixed|null the request parameter value or null if not found
	 */
	function getRequestValue($paramName) {
		return $this->mRequest->getRequestValue($paramName);
	}

	/**
	 * Get a request value by parameter name or null if not found
	 * @param Parameter\IRequestParameter $Parameter
	 * @param null $description
	 * @internal param string $paramName the parameter name
	 * @internal param string $description [optional] description for this prompt
	 * @internal param int $flags use ::PARAM_REQUIRED for required fields
	 * @return mixed the parameter value
	 */
    function getValue($Parameter, $description = null) {
        return $this->mRequest->getValue($Parameter, $description);
    }

    /**
     * Get the Request Method (GET, POST, PUT, PATCH, DELETE, or CLI)
     * @return String
     */
    function getMethodName() {
        return $this->mRequest->getMethodName();
    }

    /**
     * Return the route path for this request
     * @return String the route path starting with '/'
     */
    function getPath() {
        return $this->mRequest->getPath();
    }

    /**
     * Matches a route prefix to this request and updates the method args with any extra path
     * @param $routePrefix '[method] [path]'
     * @return bool true if the route matched
     */
    function match($routePrefix) {
        return $this->mRequest->match($routePrefix);
    }




    /**
     * Add a log entry
     * @param String $msg The log message
     * @param int $flags [optional] log flags
     * @return void
     */
    function log($msg, $flags = 0) {
        foreach($this->mLogs as $Log)
            $Log->log($msg, $flags);

        $this->mRequest->log($msg, $flags);
    }

    /**
     * Log an exception instance
     * @param \Exception $ex The log message
     * @param int $flags [optional] log flags
     * @return void
     */
    function logEx(\Exception $ex, $flags = 0) {
        foreach($this->mLogs as $Log)
            $Log->logEx($ex, $flags);

        $this->mRequest->log($ex, $flags);
    }

    /**
     * Add a log listener callback
     * @param ILogListener $Listener
     * @return void
     */
    function addLogListener(ILogListener $Listener) {
        $this->mLogs[] = $Listener;
    }

//    /**
//     * Returns an associative array of params and their descriptions
//     * @return array
//     */
//    function getParameterDescriptions() {
//        return $this->mRequest->getParameterDescriptions();
//    }

    /**
     * @param bool $withDomain
     * @return String
     */
    function getDomainPath($withDomain = false) {
        return $this->mRequest->getDomainPath($withDomain);
    }


	/**
	 * Return all request parameters collected by ::getValue
	 * @return IRequestParameter[]
	 */
	function getParameters() {
		return $this->mRequest->getParameters();
	}

	/**
	 * Get the next argument value or null if no more arguments are found
	 * @param null $index if set, returns the value at index, otherwise the next value
	 * @param bool $reset if set resets the current position to $index ?: 0
	 * @return mixed|null the argument value or null if not found
	 */
	function getArgumentValue($index = null, $reset = false) {
		return $this->mRequest->getParameters($index, $reset);
	}
}