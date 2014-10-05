<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 10/3/14
 * Time: 9:38 PM
 */
namespace CPath\Request\Parameter;

use CPath\Render\HTML\Attribute\IAttributes;
use CPath\Request\Form\IFormRequest;
use CPath\Request\IRequest;

class FormField extends Parameter
{
	public function __construct($paramName, $description = null, $defaultValue = null) {
		parent::__construct($paramName, $description, $defaultValue);
	}

	/**
	 * Validate and return the parameter value
	 * @param IRequest $Request
	 * @param $value
	 * @return mixed request value
	 */
	function validateParameter(IRequest $Request, &$value) {
		if (!$Request instanceof IFormRequest)
			return null;
		$value = $this->filter($Request, $value);
		if($value)
			$this->Input->setValue($value);
		return $value;
	}


	/**
	 * Render request as html
	 * @param IRequest $Request the IRequest instance for this render which contains the request and remaining args
	 * @param IAttributes $Attr
	 * @return String|void always returns void
	 */
	function renderHTML(IRequest $Request, IAttributes $Attr = null) {
		$this->Label->renderHTML($Request, $Attr);
	}
}