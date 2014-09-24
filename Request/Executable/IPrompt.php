<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/16/14
 * Time: 3:36 PM
 */
namespace CPath\Request\Executable;

use CPath\Describable\IDescribable;
use CPath\Request\Validation\PromptException;

interface IPrompt
{

    /**
     * Prompt for a value from the request.
     * @param string|IDescribable|null $description [optional] description for this prompt
     * @return mixed the parameter value or null on failure
     * Example:
     * $name = $Request->promptField('name', 'Please enter your name', 'MyName');  // Gets value for parameter 'name' or returns default string 'MyName'
     */
    function prompt($description = null);
}
