<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/16/14
 * Time: 10:19 PM
 */
namespace CPath\Request;

interface IRequestMethod
{
    /**
     * Get the Request Method (GET, POST, PUT, PATCH, DELETE, or CLI)
     * @return String
     */
    function getMethodName();

    /**
     * Get a request value if it exists
     * @param string $argName the parameter name
     * @return mixed the parameter value or null
     */
    function getValue($argName);

    /**
     * Checks a request value to see if it exists
     * @param string $argName the parameter name
     * @return bool
     */
    function hasValue($argName);
}