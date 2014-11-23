<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 11/15/14
 * Time: 6:21 PM
 */
namespace CPath\Request\Parameter;

use CPath\Render\HTML\Element\HTMLCheckBoxField;
use CPath\Render\HTML\Element\IHTMLInput;

class BooleanFormField extends FormField
{
	public function __construct($paramName, $description = null, $defaultValue = false) {
		parent::__construct($paramName, $description, $defaultValue);

	}

	function getHTMLInput(IHTMLInput $Input=null) {
		$Input = $Input ?: new HTMLCheckBoxField($this->getFieldName());
		$Input = parent::getHTMLInput($Input);
		return $Input;
	}
}