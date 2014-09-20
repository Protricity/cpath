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
    private $mParams;
    private $mFields;

    public function __construct($methodName, Array $pathParams=array()) {
        $this->mParams = $pathParams;
        $this->mName = $methodName;
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
        if (!empty($this->mParams[$name]))
            return $this->mParams[$name];

        $values = $this->getAllFormFieldValues();

        if (!empty($values[$name]))
            return $values[$name];

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

    /**
     * Add a path parameter i.e. /:id/
     * @param $name
     * @param $value
     * @return void
     */
    function addPathParameter($name, $value) {
        $this->mParams[$name] = $value;
    }

    /**
     * Prompt for a value from the request.
     * @param string $name the parameter name
     * @param string|null $defaultValue [optional] default value if prompt fails
     * @return mixed the parameter value
     * @throws \CPath\Request\Exceptions\RequestParameterException if a prompt failed to produce a result
     * Example:
     * $name = $Request->prompt('name', 'Please enter your name', 'MyName');  // Gets value for parameter 'name' or returns default string 'MyName'
     */
    function getFieldValue($name, $defaultValue=null) {
        $values = $this->getAllFormFieldValues();
        return !empty($values[$name]) ? $values[$name] : $defaultValue;
    }


    function getAllFormFieldValues() {
        if ($this->mFields !== null)
            return $this->mFields;

        if (WebRequest::getHeader('Content-Type') === 'application/json') {
            $input = file_get_contents('php://input');
            $this->mFields = json_decode($input, true);
            return $this->mFields;
        }

        $this->mFields = $_POST;
        return $this->mFields;
    }
}