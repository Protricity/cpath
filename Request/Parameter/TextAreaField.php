<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 11/12/14
 * Time: 12:33 PM
 */
namespace CPath\Request\Parameter;

use CPath\Render\HTML\Element\HTMLTextAreaField;

class TextAreaField extends FormField
{
	public function __construct($paramName, $description = null, $defaultValue = null) {
		parent::__construct($paramName, $description, $defaultValue);
	}

	function getHTMLInput() {
		$Input = new HTMLTextAreaField($this->getFieldName());
		$Input->setAttribute('placeholder', $this->getFieldName());
		return $Input;
	}
}

