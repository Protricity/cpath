<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/21/14
 * Time: 3:14 PM
 */
namespace CPath\Request\Web;

use CPath\Request\Parameter\Parameter;
use CPath\Request\IFormRequest;
use CPath\Request\Parameter\IRequestParameter;

class WebFormRequest extends WebRequest implements IFormRequest
{
    private $mValueSource = null;

    public function __construct($method, $path = null, $args = array()) {
        parent::__construct($method, $path, $args);
    }

	/**
	 * Return a request value
	 * @param String|IRequestParameter $Parameter string or instance
	 * @param String|null $description
	 * @return mixed the validated parameter value
	 */
	function getValue($Parameter, $description=null) {
		if(!$Parameter instanceof IRequestParameter)
			$Parameter = new Parameter($Parameter, $description);

		$this->addParam($Parameter);

		$value =
			$this->getArgumentValue($Parameter->getName()) ?:
			$this->getFormFieldValue($Parameter->getName()) ?:
			$this->getRequestValue($Parameter->getName());

		return $Parameter->validate($this, $value);
	}

	/**
	 * Return a request value
	 * @param $fieldName
	 * @return mixed the form field value
	 */
	function getFormFieldValue($fieldName) {
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