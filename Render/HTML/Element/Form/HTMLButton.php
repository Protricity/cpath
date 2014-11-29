<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 11/28/14
 * Time: 1:43 PM
 */
namespace CPath\Render\HTML\Element\Form;

use CPath\Render\HTML\Attribute\IAttributes;
use CPath\Render\HTML\IRenderHTML;
use CPath\Request\IRequest;

class HTMLButton extends HTMLFormField
{
	const NODE_TYPE = 'button';

	private $mContent;

	public function __construct($classList = null, $value = null, $name = null, $type = null) {
		$this->mContent = $value;
		parent::__construct($classList, $name, null, $type);
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