<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 10/19/14
 * Time: 10:57 AM
 */
namespace CPath\Render\HTML\Element;

use CPath\Framework\Render\Util\RenderIndents as RI;
use CPath\Render\HTML\Attribute\HTMLAttributes;
use CPath\Render\HTML\Attribute\IAttributes;
use CPath\Render\HTML\Header\IHTMLSupportHeaders;
use CPath\Render\HTML\IRenderHTML;
use CPath\Request\IRequest;

abstract class AbstractHTMLElement implements IRenderHTML
{
	const TRIM_CONTENT = false;

	private $mElmType;
	private $mAttr;

	/**
	 * @param string $elmType
	 * @param String|Array|IAttributes $classList attribute instance, class list, or attribute html
	 */
	public function __construct($elmType, $classList = null) {
		$this->mElmType = $elmType;
		if($classList instanceof IAttributes) {
			$this->mAttr = $classList;
		} else {
			$this->mAttr = new HTMLAttributes($classList);
		}
	}

	/**
	 * Render element content
	 * @param IRequest $Request
	 * @param IAttributes $ContentAttr
	 */
	abstract function renderContent(IRequest $Request, IAttributes $ContentAttr = null);

	/**
	 * Returns true if this element has an open tag
	 * @return bool
	 */
	abstract protected function isOpenTag();

	function getElementType() {
		return $this->mElmType;
	}

	function setAttribute($attrName, $attrValue) {
		$this->mAttr->setAttribute($attrName, $attrValue);
		return $this;
	}

	function getAttribute($attrName, $defaultValue = null) {
		return $this->mAttr->getAttribute($attrName, $defaultValue);
	}

	function hasAttribute($attrName) {
		return $this->mAttr->hasAttribute($attrName);
	}

	protected function removeAttribute($attrName) {
		return $this->mAttr->removeAttribute($attrName);
	}

	public function addClass($classList) {
		$this->mAttr->addClass($classList);
	}

	public function hasClass($className) {
		return $this->mAttr->hasClass($className);
	}

	/**
	 * @return IAttributes
	 */
	public function getAttributes() {
		if (!$this->mAttr instanceof HTMLAttributes)
			$this->mAttr = new HTMLAttributes($this->mAttr);
		return $this->mAttr;
	}

	/**
	 * Render request as html
	 * @param IRequest $Request the IRequest instance for this render which contains the request and remaining args
	 * @param IAttributes $Attr optional attributes for the input field
	 * @return String|void always returns void
	 */
	function renderHTML(IRequest $Request, IAttributes $Attr = null) {
		if($this->isOpenTag()) {
			echo RI::ni(), "<", $this->getElementType(), $this->getAttributes()->render($Attr), '>';
			$this->renderContent($Request);
//			if(!static::TRIM_CONTENT)
//				echo RI::ni();
			echo "</", $this->getElementType(), ">";

		} else {
			echo RI::ni(), "<", $this->getElementType(), $this->getAttributes()->render($Attr), "/>";

		}
	}
}