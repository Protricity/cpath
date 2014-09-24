<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/21/14
 * Time: 3:14 PM
 */
namespace CPath\Request\Web;

use CPath\Describable\IDescribable;

class WebFormRequest extends WebRequest
{
    private $mMethodName;
    private $mValueSource = null;


    public function __construct($method, $path = null, Array $params = array()) {
        $this->mMethodName = $method;

        if (!$path)
            $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        parent::__construct($path, $params);
    }

    /**
     * Get the Request Method (POST, PUT, PATCH, DELETE, or CLI)
     * @return String
     */
    function getMethodName() {
        return $this->mMethodName;
    }

    /**
     * Checks a request value to see if it exists
     * @param string $paramName the parameter name
     * @return bool
     */
    function hasValue($paramName) {
        if(parent::hasValue($paramName))
            return true;

        $values = $this->getAllValues();
        if(!empty($values[$paramName]))
            return true;

        return false;
    }

    /**
     * Get a request value by parameter name if it exists
     * @param string $paramName the parameter name
     * @param string|IDescribable|null $description [optional] description for this prompt
     * @return mixed the parameter value or null
     */
    function getValue($paramName, $description = null) {
        if(parent::hasValue($paramName))
            return parent::getValue($paramName);

        $values = $this->getAllValues();
        if(!empty($values[$paramName]))
            return $values[$paramName];

        return null;
    }


    function getAllValues() {
        if ($this->mValueSource !== null)
            return $this->mValueSource;

        if ($this->getHeader('Content-Type') === 'application/json') {
            $input = file_get_contents('php://input');
            $this->mValueSource = json_decode($input, true);
            return $this->mValueSource;
        }

        return $this->mValueSource = $_POST;
    }
}