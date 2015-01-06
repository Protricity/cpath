<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 11/28/14
 * Time: 1:43 PM
 */
namespace CPath\Render\HTML\Element\Form;

use CPath\Render\HTML\Attribute\IAttributes;
use CPath\Render\HTML\Element\HTMLInputField;
use CPath\Render\HTML\IRenderHTML;
use CPath\Request\IRequest;

class HTMLButton extends HTMLInputField
{
	const NODE_TYPE = 'button';

	private $mContent;

	/**
	 * @param String|null $name field name (name=[])
	 * @param String|null $value input value (value=[])
	 * @param String|null $type input type (type=[])
	 * @internal param null|String $classList a list of class elements
	 */
	public function __construct($name = null, $value = null, $type = null) {
		$this->mContent = $value ?: ucwords($name);
		parent::__construct($name, null, $type);
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