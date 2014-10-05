<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 10/3/14
 * Time: 11:14 PM
 */
namespace CPath\Request\Parameter;

use CPath\Render\HTML\Attribute\IAttributes;
use CPath\Request\Form\IFormRequest;
use CPath\Request\IRequest;
use CPath\Request\RequestException;

class PasswordField extends Parameter
{
	const PASS_BLANK = '*****';

	private $mRequired;

	public function __construct($paramName, $description=null, $required=true) {
		parent::__construct($paramName, $description);
		$this->mRequired = $required;
	}

	/**
	 * Validate and return the parameter value
	 * @param IRequest $Request
	 * @param $value
	 * @throws \CPath\Request\RequestException
	 * @return mixed request value
	 */
	function validateParameter(IRequest $Request, &$value) {
		if (!$Request instanceof IFormRequest) {
			if(!$this->mRequired)
				return null;
			$this->Label->addClass(self::CSS_CLASS_ERROR);
			throw new RequestException("Password field value must come from a form request: " . $this->getName());
		}
		if($value === self::PASS_BLANK)
			$value = null;
		$value = $this->filter($Request, $value);
		if($this->mRequired && !$value) {
			$this->Label->addClass(self::CSS_CLASS_ERROR);
			throw new RequestException("Password was not entered");
		}
		//$this->Input->setValue($value);
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

