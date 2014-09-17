<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/6/14
 * Time: 2:50 PM
 */
namespace CPath\Request;

use CPath\Describable\IDescribable;
use CPath\Request\Exceptions\RequestParameterException;
use CPath\Request\Executable\IPrompt;

interface IRequest
{
    /**
     * Get the requested Mime types for rendering purposes
     * @return \CPath\Request\MimeType\IRequestedMimeType[]
     */
    function getMimeTypes();


    /**
     * Get the Request Method Instance (GET, POST, PUT, PATCH, DELETE, or CLI)
     * @return \CPath\Request\Method\IRequestMethod
     */
    function getMethod();

    /**
     * Return the route path for this request
     * @return String the route path starting with '/'
     */
    function getPath();
//
//    /**
//     * Prompt for a value from the request.
//     * @param string $name the parameter name
//     * @param string|IDescribable|null $description [optional] description for this prompt
//     * @param string|null $defaultValue [optional] default value if prompt fails
//     * @return mixed the parameter value
//     * @throws \CPath\Request\Exceptions\RequestParameterException if a prompt failed to produce a result
//     * Example:
//     * $name = $Request->prompt('name', 'Please enter your name', 'MyName');  // Gets value for parameter 'name' or returns default string 'MyName'
//     */
//    function getParam($name, $description = null, $defaultValue = null);
}

