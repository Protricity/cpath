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

    public function __construct($method, $path = null, $parameters = array()) {
        parent::__construct($method, $path, $parameters);
    }

	public function getWebRequest() {
		return new WebRequest($this->getMethodName(), $this->getPath(), $this->getParameterValues());
	}

	protected function getAllFormValues() {
		if ($this->mValueSource !== null)
			return $this->mValueSource;

		list($type) = explode(';', $this->getHeader('Content-Type'), 2);
		if (strcasecmp($type, 'application/json') === 0) {
			$input = file_get_contents('php://input');
			$this->mValueSource = json_decode($input, true);
			return $this->mValueSource;
		}
		if(!$_POST
			&& $input = file_get_contents('php://input')) {
			$vars = array();
			parse_str($input, $vars);
			$this->log('$_POST data not available. input parsed from php://input', static::ERROR || static::VERBOSE);
			return $this->mValueSource = $vars;
		}

		return $this->mValueSource = $_POST;
	}

	/**
	 * Return a request parameter (GET) value
	 * @param String $paramName
	 * @return mixed|null the request parameter value or null if not found
	 */

	function getRequestValue($paramName) {
		$value = parent::getRequestValue($paramName);
		if($value !== null)
			return $value;

		$values = $this->getAllFormValues();
		return $this->getNamedRequestValue($paramName, $values);
	}


	/**
	 * Return a request value
	 * @param $fieldName
	 * @return mixed the form field value
	 */
	function getFormFieldValue($fieldName) {
		$values = $this->getAllFormValues();
		return $this->getNamedRequestValue($fieldName, $values);
	}

}