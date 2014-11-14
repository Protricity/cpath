<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/21/14
 * Time: 3:14 PM
 */
namespace CPath\Request\Web;

use CPath\Request\Form\IFormRequest;

class WebFormRequest extends WebRequest implements IFormRequest
{
    private $mValueSource = null;

    public function __construct($method, $path = null, $parameters = array()) {
        parent::__construct($method, $path, $parameters);
    }

	/**
	 * Return a request parameter (GET) value
	 * @param String $paramName
	 * @return mixed|null the request parameter value or null if not found
	 */

	function getRequestValue($paramName) {
		$values = $this->getAllFormValues();
		return isset($values[$paramName])
			? $values[$paramName]
			: parent::getRequestValue($paramName);
	}


	/**
	 * Return a request value
	 * @param $fieldName
	 * @return mixed the form field value
	 */
	function getFormFieldValue($fieldName) {
		$values = $this->getAllFormValues();
		if(isset($values[$fieldName]))
			return $values[$fieldName];
		return null;
	}

    protected function getAllFormValues() {
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