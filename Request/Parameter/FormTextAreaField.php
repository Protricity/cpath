<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 11/12/14
 * Time: 12:33 PM
 */
namespace CPath\Request\Parameter;

use CPath\Render\HTML\Element\HTMLTextAreaField;
use CPath\Render\HTML\Element\IHTMLInput;
use CPath\Request\IRequest;

class FormTextAreaField extends FormField
{
	public function __construct($paramName, $description = null, $defaultValue = null) {
		parent::__construct($paramName, $description, $defaultValue);
	}

	protected function getHTMLInput(IRequest $Request, IHTMLInput $Input=null) {
		$Input = $Input ?: new HTMLTextAreaField();
		return parent::getHTMLInput($Request, $Input);
	}
}