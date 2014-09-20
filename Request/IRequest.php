<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/6/14
 * Time: 2:50 PM
 */
namespace CPath\Request;

interface IRequest
{
    /**
     * Get the requested Mime types for rendering purposes
     * @return \CPath\Request\MimeType\IRequestedMimeType[]
     */
    function getMimeTypes();


    /**
     * Get the Request Method Instance (GET, POST, PUT, PATCH, DELETE, or CLI)
     * @return IRequestMethod
     */
    function getMethod();

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

