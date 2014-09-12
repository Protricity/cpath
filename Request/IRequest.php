<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/6/14
 * Time: 2:50 PM
 */
namespace CPath\Request;

interface IRequest extends IPromptRequest
{
    /**
     * Return the route path for this request
     * @return String the route path starting with '/'
     */
    function getPath();

    /**
     * Get the requested Mime types
     * @return \CPath\Request\MimeType\IRequestedMimeType[]
     */
    function getMimeTypes();

    /**
     * Get the Request Method (GET, POST, PUT, PATCH, DELETE, or CLI)
     * @return String
     */
    function getMethodName();
}

