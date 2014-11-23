<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/6/14
 * Time: 2:50 PM
 */
namespace CPath\Request;

use CPath\Request\Exceptions\RequestException;
use CPath\Request\Log\ILogListener;
use CPath\Request\Parameter\IRequestParameter;

interface IRequest extends ILogListener, \ArrayAccess
{
	/**
	 * Get the requested Mime type for rendering purposes
	 * @return \CPath\Request\MimeType\IRequestedMimeType
	 */
	function getMimeType();

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

//	/**
//	 * Get the next argument value or null if no more arguments are found
//	 * @param null $index if set, returns the value at index, otherwise the next value
//	 * @param bool $reset if set resets the current position to $index ?: 0
//	 * @return mixed|null the argument value or null if not found
//	 */
//	function getArgumentValue($index=null, $reset=false);

//	/**
//	 * Return a request parameter (GET) value
//	 * @param $paramName
//	 * @return mixed|null the request parameter value or null if not found
//	 */
//	function getRequestValue($paramName);

//	/**
//	 * Return a request value
//	 * @param String|IRequestParameter $Parameter string or inst
//	 * @param null $description
//	 * @return mixed the validated parameter value
//	 */
//	function getValue($Parameter, $description = null);
////
//	/**
//	 * Return all request parameters collected by ::getValue
//	 * @return IRequestParameter[]
//	 */
//	function getParameters();

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
     * Get a request value by parameter name or throws an exception if not found
     * @param string $paramName the parameter name
     * @param string $description [optional] description for this prompt
     * @return mixed the parameter value
     * @throws RequestException if the value was not found
     */
//     * @param int $flags use ::PARAM_REQUIRED for required fields
    //function getValue($paramName, $description = null);

	/**
	 * @param IRequestParameter $Parameter
	 * @return mixed the validated parameter value
	 * @throws RequestException if the value was not found
	 */
	//function getParameterValue(IRequestParameter $Parameter);


	/**
	 * Return a request value
	 * @param $paramName
	 * @param $description
	 * @internal param \CPath\Request\Parameter\IRequestParameter $Parameter
	 * @return mixed the validated parameter value
	 */
	//function getParameterValue($paramName, $description);

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
    //function setRequestParameters(IParameterMap $Map);
}

