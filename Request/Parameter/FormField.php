<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 10/3/14
 * Time: 9:38 PM
 */
namespace CPath\Request\Parameter;

use CPath\Request\Form\IFormRequest;
use CPath\Request\IRequest;

class FormField extends Parameter
{
	public function __construct($paramName, $description = null, $defaultValue = null) {
		parent::__construct($paramName, $description, $defaultValue);
	}

	/**
	 * Get the request value
	 * @param \CPath\Request\IRequest $Request
	 * @return mixed
	 */
	function getRequestValue(IRequest $Request) {
		if($Request instanceof IFormRequest)
			return $Request->getFormFieldValue($this->getFieldName());
		return parent::getRequestValue($Request);
	}

}

