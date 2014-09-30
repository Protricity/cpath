<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/21/14
 * Time: 11:11 PM
 */
namespace CPath\Request;

use CPath\Describable\IDescribable;
use CPath\Request\Log\ILogListener;

abstract class RequestWrapper implements IRequest
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
     * Get a request value by parameter name or null if not found
     * @param string $paramName the parameter name
     * @param string $description [optional] description for this prompt
     * @param int $flags use ::PARAM_REQUIRED for required fields
     * @return mixed the parameter value
     * @throws RequestException if the value was not found
     */
    function getValue($paramName, $description = null, $flags=0) {
        return $this->mRequest->getValue($paramName, $description);
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

    /**
     * Returns an associative array of params and their descriptions
     * @return array
     */
    function getParameterDescriptions() {
        return $this->mRequest->getParameterDescriptions();
    }

    /**
     * @param bool $withDomain
     * @return String
     */
    function getDomainPath($withDomain = false) {
        return $this->mRequest->getDomainPath($withDomain);
    }
}