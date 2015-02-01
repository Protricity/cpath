<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/6/14
 * Time: 2:50 PM
 */
namespace CPath\Request;

use CPath\Request\Log\ILogListener;

interface IRequest extends ILogListener, \ArrayAccess, \IteratorAggregate
{
	const MATCH_SESSION_ONLY = 0x010;
	const MATCH_NO_SESSION   = 0x020;

	const NAVIGATION_ROUTE   = 0x100;

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
	 * @param int $flags
	 * @return bool true if the route matched
	 */
	function match($routePrefix, $flags=0);

}

