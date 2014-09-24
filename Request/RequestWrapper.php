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
use CPath\Request\MimeType\IRequestedMimeType;

abstract class RequestWrapper implements IRequest
{
    private $mRequest;
    /** @var ILogListener[] */
    private $mLogs=array();

    function __construct(IRequest $Request) {
        $this->mRequest = $Request;
    }

    /**
     * Get the requested Mime types for rendering purposes
     * @return \CPath\Request\MimeType\IRequestedMimeType[]
     */
    function getMimeType() {
        return $this->mRequest->getMimeType();
    }

    /**
     * Set the requested Mime type for this request
     * @param MimeType\IRequestedMimeType $MimeType
     * @return void
     */
    function setMimeType(IRequestedMimeType $MimeType) {
        $this->mRequest->setMimeType($MimeType);
    }

    /**
     * Checks a request value to see if it exists
     * @param string $paramName the parameter name
     * @return bool
     */
    function hasValue($paramName) {
        return $this->mRequest->hasValue($paramName);
    }

    /**
     * Get a request value by parameter name if it exists
     * @param string $paramName the parameter name
     * @param string|IDescribable|null $description [optional] description for this prompt
     * @return mixed the parameter value or null
     */
    function getValue($paramName, $description = null) {
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
}