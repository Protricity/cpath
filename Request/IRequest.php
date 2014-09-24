<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/6/14
 * Time: 2:50 PM
 */
namespace CPath\Request;

use CPath\Describable\IDescribable;
use CPath\Request\Exceptions\RequestArgumentException;
use CPath\Request\Log\ILogListener;
use CPath\Request\MimeType\IRequestedMimeType;

interface IRequest extends ILogListener
{
    /**
     * Get the requested Mime type for rendering purposes
     * @return \CPath\Request\MimeType\IRequestedMimeType
     */
    function getMimeType();

    /**
     * Set the requested Mime type for this request
     * @param MimeType\IRequestedMimeType $MimeType
     * @return void
     */
    function setMimeType(IRequestedMimeType $MimeType);

    /**
     * Checks a request value to see if it exists
     * @param string $paramName the parameter name
     * @return bool
     */
    function hasValue($paramName);

    /**
     * Get a request value by parameter name if it exists
     * @param string $paramName the parameter name
     * @param string|IDescribable|null $description [optional] description for this prompt
     * @return mixed the parameter value or null
     */
    function getValue($paramName, $description = null);

    /**
     * Get the Request Method (GET, POST, PUT, PATCH, DELETE, or CLI)
     * @return String
     */
    function getMethodName();

    /**
     * Return the route path for this request
     * @return String the route path starting with '/'
     */
    function getPath();

    /**
     * Matches a route prefix to this request and updates the method args with any extra path
     * @param $routePrefix '[method] [path]'
     * @return bool true if the route matched
     */
    function match($routePrefix);
}

