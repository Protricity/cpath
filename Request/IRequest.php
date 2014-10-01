<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/6/14
 * Time: 2:50 PM
 */
namespace CPath\Request;

use CPath\Request\Log\ILogListener;
use CPath\Request\Parameter\IParameterMap;

interface IRequest extends ILogListener, IParameterMap
{
    const PARAM_REQUIRED = 0x01;
    const PARAM_ERROR = 0x02;

    const PARAM_TEXTAREA = 0x10;

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
    //function setMimeType(IRequestedMimeType $MimeType);

    /**
     * Checks a request value to see if it exists
     * @param string $paramName the parameter name
     * @return bool
     */
    //function hasValue($paramName);

    /**
     * Get a request value by parameter name or null if not found
     * @param string $paramName the parameter name
     * @param string $description [optional] description for this prompt
     * @param int $flags use ::PARAM_REQUIRED for required fields
     * @return mixed the parameter value
     * @throws RequestException if the value was not found
     */
    function getValue($paramName, $description = null, $flags=0);

    /**
     * Get a request value by parameter name or throw an exception
     * @param string $paramName the parameter name
     * @param string $description [optional] description for this prompt
     * @return mixed the parameter value
     * @throws RequestException if the value was not found
     */
    //function requireValue($paramName, $description = null);


    /**
     * Set the request parameters expected by this request
     * @param IParameterMap $Map
     */
    function setRequestParameters(IParameterMap $Map);

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
     * @param bool $withDomain
     * @return String
     */
    function getDomainPath($withDomain=false);

    /**
     * Matches a route prefix to this request and updates the method args with any extra path
     * @param $routePrefix '[method] [path]'
     * @return bool true if the route matched
     */
    function match($routePrefix);
}

