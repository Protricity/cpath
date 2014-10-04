<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 10/3/14
 * Time: 9:37 PM
 */
namespace CPath\Request\Parameter;

use CPath\Request\IFormRequest;
use CPath\Request\IRequest;
use CPath\Request\Parameter\Parameter;
use CPath\Request\RequestException;

class RequiredFormField extends FormField
{
	public function __construct($paramName, $description = null, $defaultValue = null) {
		parent::__construct($paramName, $description, $defaultValue);
	}

	/**
	 * Validate and return the parameter value
	 * @param IRequest $Request
	 * @param $value
	 * @throws \CPath\Request\RequestException
	 * @return mixed request value
	 */
	function validate(IRequest $Request, $value) {
		if (!$Request instanceof IFormRequest) {
			$this->Label->addClass(self::CSS_CLASS_ERROR);
			throw new RequestException("Required Form field must come from a form request: " . $this->getName());
		}
		$value =
			//parent::validate($Request, $value) ?:
			$Request->getArgumentValue($this->getName()) ?: // Block out non-POST but allow arguments
			$Request->getFormFieldValue($this->getName());
		if (!$value) {
			$this->Label->addClass(self::CSS_CLASS_ERROR);
			throw new RequestException("Form field is required: " . $this->getName());
		}
		return $value;
	}
}

