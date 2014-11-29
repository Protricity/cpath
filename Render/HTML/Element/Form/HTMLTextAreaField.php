<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/29/14
 * Time: 10:38 PM
 */
namespace CPath\Render\HTML\Element\Form;

use CPath\Render\HTML\Attribute\IAttributes;
use CPath\Render\HTML\IRenderHTML;
use CPath\Request\IRequest;

class HTMLTextAreaField extends HTMLFormField {

	const NODE_TYPE = 'textarea';
	const TRIM_CONTENT = true;

	private $mText;

	/**
	 * @param String|Array|IAttributes $classList attribute inst, class list, or attribute html
	 * @param null $name
	 * @param null $value
	 * @internal param null $description
	 */
	public function __construct($classList = null, $name = null, $value = null) {
		parent::__construct($classList, $name, $value);
	}

	public function getInputValue()                     { return $this->mText; }
	public function setInputValue($text)                { $this->mText = $text; }

	/**
	 * Render element content
	 * @param IRequest $Request
	 * @param IAttributes $ContentAttr
	 * @param \CPath\Render\HTML\IRenderHTML $Parent
	 */
	function renderContent(IRequest $Request, IAttributes $ContentAttr = null, IRenderHTML $Parent = null) {
		echo $this->mText;
	}

	/**
	 * Returns true if this element has an open tag
	 * @return bool
	 */
	protected function isOpenTag() {
		return true;
	}

	// Static
//
//	/**
//	 * @param null $description
//	 * @param String|Array|IAttributes $classList attribute inst, class list, or attribute html
//	 * @param null $name
//	 * @param null $value
//	 * @return \CPath\Render\HTML\Element\Form\HTMLFormField
//	 */
//	static function get($description = null, $classList = null, $name = null, $value = null) {
//		return new HTMLTextAreaField($description, $classList, $name, $value);
//	}
}