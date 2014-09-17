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
    private $mCommandLine;

    public function __construct(Array $argv=null) {
        if($argv === null)
            $argv = $_SERVER['argv'];
        $this->mCommandLine = new CommandString($argv);
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
    function promptField($name, $description = null, $defaultValue = null) {
        if($this->mCommandLine->hasOption($name))
            return $this->mCommandLine->getOption($name);

        $line = readline($description); // readline_add_history
        if($line)
            return $line;

        if($defaultValue !== null)
            return $defaultValue;

        throw new RequestParameterException("GET parameter '" . $name . "' not set", $name, $description);
    }

    /**
     * Prompt for an argument value from the request.
     * @param string|IDescribable|null $description [optional] description for this prompt
     * @param string|null $defaultValue [optional] default value if prompt fails
     * @return mixed the parameter value
     * @throws \CPath\Request\Exceptions\RequestArgumentException if a prompt failed to produce a result
     */
    function prompt($description, $defaultValue = null) {
        $arg = $this->mCommandLine->getNextArg();
        if($arg !== null)
            return $arg;

        $line = readline($description); // readline_add_history
        if($line)
            return $line;

        if($defaultValue !== null)
            return $defaultValue;

        throw new RequestArgumentException("GET argument not set", $description);
    }
}