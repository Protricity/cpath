<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 11/15/14
 * Time: 3:17 PM
 */
namespace CPath\Render\HTML\Element;

use CPath\Render\HTML\Attribute\AttributeCollection;
use CPath\Render\HTML\Attribute\HTMLTabStopAttribute;
use CPath\Render\HTML\Attribute\IAttributes;

class HTMLFormSection extends HTMLElement
{
	//const CSS_CONTENT_CLASS = HTMLForm::CSS_CONTENT_CLASS;
	const CSS_CLASS         = HTMLForm::CSS_FORM_SECTION;

	public $Legend;

	/**
	 * @param string $legendText
	 * @param String|Array|IAttributes $classList attribute inst, class list, or attribute html
	 * @param String|null $_content [optional] varargs of content
	 */
	public function __construct($legendText, $classList = null, $_content = null) {
		$classList = AttributeCollection::combine(new HTMLTabStopAttribute(), $classList);
		parent::__construct('fieldset', $classList);
		$this->Legend = new HTMLElement('legend', null, $legendText);
		$this->addContent($this->Legend);
		$this->addAll(array_slice(func_get_args(), 2));
	}

}

