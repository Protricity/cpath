<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/7/14
 * Time: 12:47 PM
 */
namespace CPath\Request\Web;

use CPath\Request\Exceptions\FormFieldException;
use CPath\Request\Web;

final class FormRequest extends AbstractWebRequest implements IFormRequest
{
    private $mMethod;
    private $mValues = null;

    public function __construct($methodName=null) {
        $this->mMethod = $methodName ?: $_SERVER["REQUEST_METHOD"];
    }

    function getAllFormFieldValues() {
        if ($this->mValues !== null)
            return $this->mValues;

        if($this->getHeader('Content-Type') === 'application/json') {
            $input = file_get_contents('php://input');
            $this->mValues = json_decode($input, true);
            return $this->mValues;
        }

        $this->mValues = $_POST;
        return $this->mValues;
    }

    /**
     * Get the Request Method (GET, POST, PUT, PATCH, DELETE)
     * @return String
     */
    function getMethodName() {
        return $this->mMethod;
    }

    /**
     * Prompt for a form field value by name
     * @param string $name the form field name
     * @param string|null $description optional description for this form field
     * @param string|null $defaultValue optional default value if prompt fails
     * @return string the parameter value
     * @throws FormFieldException if a prompt failed to produce a result
     */
    function promptForm($name, $description = null, $defaultValue = null) {
        $values = $this->getAllFormFieldValues();
        if(!empty($values[$name]))
            return $values[$name];

        if($defaultValue !== null)
            return $defaultValue;

        throw new FormFieldException("GET parameter '" . $name . "' not set", $name);
    }
}