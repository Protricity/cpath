<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/16/14
 * Time: 10:19 PM
 */
namespace CPath\Request\Web;

use CPath\Describable\IDescribable;
use CPath\Request\Exceptions\RequestArgumentException;
use CPath\Request\Exceptions\RequestParameterException;
use CPath\Request\IRequestMethod;

class POSTMethod implements IRequestMethod
{
    private $mName;
    private $mArgs;
    private $mArgPos = 0;

    public function __construct($methodName, Array $args=array()) {
        $this->mArgs = $args;
        $this->mName = $methodName;
    }

    /**
     * Prompt for an argument value from the request.
     * @param string|IDescribable|null $description [optional] description for this prompt
     * @param string|null $defaultValue [optional] default value if prompt fails
     * @throws \CPath\Request\Exceptions\RequestArgumentException
     * @return mixed the parameter value
     */
    function prompt($description, $defaultValue = null) {
        if(isset($this->mArgs[$this->mArgPos]))
            return $this->mArgs[$this->mArgPos++];

        throw new RequestArgumentException($this->getMethodName() . "GET path argument required", $description);
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
        if (!empty($_GET[$name]))
            return $_GET[$name];

        if ($defaultValue !== null)
            return $defaultValue;

        throw new RequestParameterException($this->getMethodName() . " form field '" . $name . "' not set", $name, $description);
    }

    /**
     * Get the Request Method (GET, POST, PUT, PATCH, DELETE, or CLI)
     * @return String
     */
    function getMethodName() {
        return $this->mName;
    }
}