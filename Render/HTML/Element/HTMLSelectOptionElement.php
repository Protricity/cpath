<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 10/19/14
 * Time: 10:28 AM
 */
namespace CPath\Render\HTML\Element;

use CPath\Render\HTML\Common\HTMLText;

class HTMLSelectOptionElement extends HTMLElement
{
	const TRIM_CONTENT     = true;

	public function __construct($value, $description = null, $selected=false, $classList = null) {
		parent::__construct('option', $classList);
		$this->addContent(new HTMLText($description ? : $value));
		if ($description)
			$this->setAttribute('value', $value);
		if ($selected)
			$this->setAttribute('selected', 'selected');
	}
}