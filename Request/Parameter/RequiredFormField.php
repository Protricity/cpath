<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 10/3/14
 * Time: 9:37 PM
 */
namespace CPath\Request\Parameter;

use CPath\Request\Form\IFormRequest;
use CPath\Request\IRequest;
use CPath\Request\Exceptions\RequestException;

class RequiredFormField extends FormField
{
	public function __construct($paramName, $description = null, $defaultValue = null) {
		parent::__construct($paramName, $description, $defaultValue);
	}

	/**
	 * Validate and return the parameter value
	 * @param IRequest $Request
	 * @throws \CPath\Request\Exceptions\RequestException
	 * @internal param $value
	 * @return mixed request value
	 */
	function validateRequest(IRequest $Request) {
		if (!$Request instanceof IFormRequest) {
			$this->Label->addClass(self::CSS_CLASS_ERROR);
			throw new RequestException("Required Form field must come from a form request: " . $this->getName());
		}
		$value = $Request->getFormFieldValue($this->getName());
		/** @var IRequest $Request */
		$value = $this->filter($Request, $value);
		if (!$value) {
			$this->Label->addClass(self::CSS_CLASS_ERROR);
			throw new RequestException("Form field is required: " . $this->getName());
		}
		$this->Input->setValue($value);
		return $value;
	}

}

