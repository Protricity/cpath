<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/19/14
 * Time: 8:51 PM
 */
namespace CPath\Request\Common;

use CPath\Describable\IDescribable;
use CPath\Request\IRequest;

class ExceptionRequest implements IRequest
{
    private $mEx;
    private $mRequest;

    function __construct(\Exception $Ex, IRequest $OriginalRequest) {
        $this->mEx = $Ex;
        $this->mRequest = $OriginalRequest;
    }

    public function getOriginalRequest() {
        return $this->mRequest;
    }

    public function getException() {
        return $this->mEx;
    }

    /**
     * Matches a route prefix to this request
     * @param $routePrefix '[method] [path]'
     * @return bool true if the route matched
     */
    function match($routePrefix) {
        if (strpos($routePrefix, 'ERR') !== 0)
            return false;

        list(, $path) = explode(' ', $routePrefix, 2);

        if (strpos($this->getPath(), $path) !== 0)
            return false;

        return true;
    }

    /**
     * Get the requested Mime types for rendering purposes
     * @return \CPath\Request\MimeType\IRequestedMimeType[]
     */
    function getMimeType() {
        return $this->mRequest->getMimeType();
    }

    /**
     * Return the route path for this request
     * @return String the route path starting with '/'
     */
    function getPath() {
        return $this->mRequest->getPath();
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
}