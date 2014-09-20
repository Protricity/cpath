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

class GETMethod implements IRequestMethod
{
    private $mParams;
    public function __construct(Array $pathParams=array()) {
        $this->mParams = $pathParams;
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
        if (!empty($_GET[$name]))
            return $_GET[$name];

        if ($defaultValue !== null)
            return $defaultValue;

        throw new RequestParameterException($this->getMethodName() . " parameter '" . $name . "' not set", $name, $description);
    }

    /**
     * Get the Request Method (GET, POST, PUT, PATCH, DELETE, or CLI)
     * @return String
     */
    function getMethodName() {
        return 'GET';
    }

    /**
     * Add a path parameter i.e. /:id/
     * @param $name
     * @param $value
     * @return void
     */
    function addPathParameter($name, $value) {
        $this->mParams[$name] = $value;
    }
}
