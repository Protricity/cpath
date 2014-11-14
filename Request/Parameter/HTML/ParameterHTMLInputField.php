<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 11/13/14
 * Time: 6:02 PM
 */
namespace CPath\Request\Parameter\HTML;

use CPath\Render\HTML\Element\HTMLInputField;
use CPath\Request\Parameter\IRequestParameter;

class ParameterHTMLInputField extends HTMLInputField
{
	private $mParameter;

	public function __construct(IRequestParameter $Parameter, $classList = null) {
		$this->mParameter = $Parameter;
		parent::__construct($Parameter->getFieldName(), null, null, $classList);
	}

	public function getParameter() {
		return $this->mParameter;
	}
}