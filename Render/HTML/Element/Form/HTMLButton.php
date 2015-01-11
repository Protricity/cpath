<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 11/28/14
 * Time: 1:43 PM
 */
namespace CPath\Render\HTML\Element\Form;

use CPath\Render\HTML\Attribute\IAttributes;
use CPath\Render\HTML\Header\IHTMLSupportHeaders;
use CPath\Render\HTML\IRenderHTML;
use CPath\Request\IRequest;
use CPath\Request\Validation\IValidation;

class HTMLButton extends HTMLInputField
{
	const NODE_TYPE = 'button';

	private $mContent;

	/**
	 * @param String|null $name field name (name=[])
	 * @param mixed $content
	 * @param String|null $value input value (value=[])
	 * @param String|null $type input type (type=[])
	 * @param String|null $classList a list of element classes
	 * @param String|null|Array|IAttributes|IHTMLSupportHeaders|IValidation $_content [varargs] class as string, array, or IValidation || IAttributes instance
	 */
	public function __construct($name = null, $content = null, $value = null, $type = null, $classList = null, $_content = null) {
		$this->mContent = $content;
		parent::__construct($name, null, $type);

		is_scalar($value)       ? $this->setAttribute('value', $value) : $this->addVarArg($value);
		is_scalar($name)        ?: $this->addVarArg($name);
		is_scalar($type)        ?: $this->addVarArg($type);
		is_scalar($classList)   ? $this->addClass($classList)   : $this->addVarArg($classList);

		for($i=4; $i<func_num_args(); $i++)
			$this->addVarArg(func_get_arg($i));
	}

//	public function getInputValue()                     { return $this->mContent; }
	public function setInputValue($content)             {
		// Doesn't keep track of value
	}

	/**
	 * Render element content
	 * @param IRequest $Request
	 * @param IAttributes $ContentAttr
	 * @param \CPath\Render\HTML\IRenderHTML $Parent
	 */
	function renderContent(IRequest $Request, IAttributes $ContentAttr = null, IRenderHTML $Parent = null) {
		echo $this->mContent ?: ucwords($this->getFieldName());
	}

	/**
	 * Returns true if this element has an open tag
	 * @return bool
	 */
	protected function isOpenTag() {
		return true;
	}
}