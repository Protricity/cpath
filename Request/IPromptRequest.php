<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/10/14
 * Time: 2:46 PM
 */
namespace CPath\Request;

interface IPromptRequest
{
    /**
     * Get a parameter value by name
     * @param string $name the parameter name
     * @param string|null $description optional description for this parameter
     * @param string|null $defaultValue optional default value if prompt fails
     * @return string the parameter value
     * @throws \CPath\Request\Exceptions\RequestParameterException if a prompt failed to produce a result
     */
    function prompt($name, $description = null, $defaultValue = null);
}