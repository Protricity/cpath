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
	 * @param String|null $value input value (value=[])
	 * @param String|null $type input type (type=[])
	 * @param String|null $classList a list of element classes
	 * @param String|null|Array|IAttributes|IHTMLSupportHeaders|IValidation $_content [varargs] class as string, array, or IValidation || IAttributes instance
	 */
	public function __construct($name = null, $value = null, $type = null, $classList = null, $_content = null) {
		$this->mContent = $value ?: ucwords($name);
		parent::__construct($name, null, $type);

		is_string($name)        ?: $this->addVarArg($name);
		is_string($type)        ?: $this->addVarArg($type);
		is_string($classList)   ? $this->addClass($classList)   : $this->addVarArg($classList);

		for($i=4; $i<func_num_args(); $i++)
			$this->addVarArg(func_get_arg($i));
	}

	public function getInputValue()                     { return $this->mContent; }
	public function setInputValue($content)             { $this->mContent = $content; }

	/**
	 * Render element content
	 * @param IRequest $Request
	 * @param IAttributes $ContentAttr
	 * @param \CPath\Render\HTML\IRenderHTML $Parent
	 */
	function renderContent(IRequest $Request, IAttributes $ContentAttr = null, IRenderHTML $Parent = null) {
		echo $this->mContent;
	}

	/**
	 * Returns true if this element has an open tag
	 * @return bool
	 */
	protected function isOpenTag() {
		return true;
	}
}