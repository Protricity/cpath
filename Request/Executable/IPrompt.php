<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/16/14
 * Time: 3:36 PM
 */
namespace CPath\Request\Executable;

use CPath\Describable\IDescribable;

interface IPrompt
{

    /**
     * Prompt for an argument value from the request.
     * @param string|IDescribable|null $description [optional] description for this prompt
     * @param string|null $defaultValue [optional] default value if prompt fails
     * @return mixed the parameter value
     * @throws \CPath\Request\Exceptions\RequestParameterException if a prompt failed to produce a result
     */
    function prompt($description, $defaultValue = null);

    /**
     * Prompt for a value from the request.
     * @param string $name the parameter name
     * @param string|IDescribable|null $description [optional] description for this prompt
     * @param string|null $defaultValue [optional] default value if prompt fails
     * @return mixed the parameter value
     * @throws \CPath\Request\Exceptions\RequestParameterException if a prompt failed to produce a result
     * Example:
     * $name = $Request->promptField('name', 'Please enter your name', 'MyName');  // Gets value for parameter 'name' or returns default string 'MyName'
     */
    function promptField($name, $description = null, $defaultValue = null);
}