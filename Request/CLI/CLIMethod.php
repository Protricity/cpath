<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/16/14
 * Time: 10:29 PM
 */
namespace CPath\Request\CLI;

use CPath\Describable\IDescribable;
use CPath\Request\Exceptions\RequestArgumentException;
use CPath\Request\Exceptions\RequestParameterException;
use CPath\Request\IRequestMethod;

class CLIMethod implements IRequestMethod
{
    private $mArgs;
    private $mArgPos = 0;

    public function __construct(Array $args=array()) {
        $this->mArgs = $args;
    }

    /**
     * Get the Request Method (CLI)
     * @return String
     */
    function getMethodName() {
        return 'CLI';
    }

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
    function prompt($name, $description = null, $defaultValue = null) {
        if(isset($this->mArgs[$name]))
            return $this->mArgs[$name];

        $line = readline($description); // readline_add_history
        if($line)
            return $line;

        if($defaultValue !== null)
            return $defaultValue;

        throw new RequestParameterException("GET parameter '" . $name . "' not set", $name, $description);
    }

}