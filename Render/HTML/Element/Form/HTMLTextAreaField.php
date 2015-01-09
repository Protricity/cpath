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
use CPath\Request\Validation\IValidation;

class HTMLTextAreaField extends HTMLInputField {

	const NODE_TYPE = 'textarea';
	const TRIM_CONTENT = true;

	private $mText;

	/**
	 * @param String|null $name field name (name=[])
	 * @param String|null $value input value (value=[])
	 * @param String|null $classList a list of element classes
	 * @param String|null|Array|IAttributes|IValidation $_validation [varargs] attribute html as string, array, or IValidation || IAttributes instance
	 */
	public function __construct($name = null, $value = null, $classList = null, $_validation = null) {
		parent::__construct($name, $value);

		is_string($name)        ?: $this->addVarArg($name);
		is_string($value)       ?: $this->addVarArg($value);
		is_string($classList)   ? $this->addClass($classList)   : $this->addVarArg($classList);

		for($i=3; $i<func_num_args(); $i++)
			$this->addVarArg(func_get_arg($i));
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