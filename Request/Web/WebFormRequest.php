<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/21/14
 * Time: 3:14 PM
 */
namespace CPath\Request\Web;

use CPath\Request\Form\IFormRequest;
use CPath\Request\Parameter\IRequestParameter;
use CPath\Request\Parameter\Parameter;

class WebFormRequest extends WebRequest implements IFormRequest
{
    private $mValueSource = null;

    public function __construct($method, $path = null, $args = array()) {
        parent::__construct($method, $path, $args);
    }

	/**
	 * Return a request value
	 * @param String|IRequestParameter $Parameter string or instance
	 * @param null $description
	 * @internal param null|String $description
	 * @return mixed the validated parameter value
	 */
	function getValue($Parameter, $description = null) {
		$this->addParam($Parameter);
		return $Parameter->validateRequest($this);
	}

	/**
	 * Return a request value
	 * @param $fieldName
	 * @return mixed the form field value
	 */
	function getFormFieldValue($fieldName) {
		if($value = $this->getArgumentValue($fieldName))
			return $value;

		$values = $this->getAllFormValues();
		if(!empty($values[$fieldName]))
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