<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 10/19/14
 * Time: 10:57 AM
 */
namespace CPath\Render\HTML\Element;

use CPath\Render\Helpers\RenderIndents as RI;
use CPath\Render\HTML\Attribute\AttributeCollection;
use CPath\Render\HTML\Attribute\Attributes;
use CPath\Render\HTML\Attribute\ClassAttributes;
use CPath\Render\HTML\Attribute\IAttributes;
use CPath\Render\HTML\IRenderHTML;
use CPath\Request\IRequest;
use CPath\Request\Log\ILogListener;

abstract class AbstractHTMLElement implements IRenderHTML
{
	const CSS_CLASS = null;
	const CSS_CONTENT_CLASS = null;
	const PASS_DOWN_ATTRIBUTES = false;

	private $mElmType;
	private $mAttributes;

	/** @var ILogListener[] */
	private $mLogListeners = array();

	/**
	 * @param string $elmType
	 * @param String|Array|IAttributes $classList attribute inst, class list, or attribute html
	 */
	public function __construct($elmType, $classList = null) {
		$this->mElmType = $elmType;
		$this->mAttributes = new Attributes();

		if($classList instanceof IAttributes) {
			$this->addAttributes($classList);
		} else {
			$this->mAttributes->addHTML($classList);
		}
	}

	function addAttributes(IAttributes $Attributes, IAttributes $_Attributes=null) {
		foreach(func_get_args() as $Attributes) {
			$this->mAttributes->addAttributes($Attributes);
		}
	}

	/**
	 * Render element content
	 * @param IRequest $Request
	 * @param IAttributes $ContentAttr
	 * @param \CPath\Render\HTML\IHTMLContainer|\CPath\Render\HTML\IRenderHTML $Parent
	 */
	abstract function renderContent(IRequest $Request, IAttributes $ContentAttr = null, IRenderHTML $Parent = null);

	/**
	 * Returns true if this element has an open tag
	 * @return bool
	 */
	abstract protected function isOpenTag();

	function getElementType() {
		return $this->mElmType;
	}

	function setAttribute($attrName, $attrValue) {
		$this->mAttributes->setAttribute($attrName, $attrValue);
		return $this;
	}

	function getAttribute($attrName, $defaultValue = null) {
		return $this->mAttributes->getAttribute($attrName, $defaultValue);
	}

//	function hasAttribute($attrName) {
//		return $this->mAttributes->hasAttribute($attrName);
//	}

	protected function removeAttribute($attrName) {
		return $this->mAttributes->removeAttribute($attrName);
	}

	public function addClass($classList) {
		$this->mAttributes->addClass($classList);
	}

	public function hasClass($className) {
		return in_array($className, $this->getClasses());
	}

	public function getClasses() {
		$classes = $this->mAttributes->getClasses();
		return $classes;
	}

	/**
	 * @return IAttributes
	 */
	public function getAttributes() {
		return $this->mAttributes;
	}

	/**
	 * Render HTML element and content
	 * @param IRequest $Request the IRequest inst for this render which contains the request and remaining args
	 * @param IAttributes $Attr optional attributes for the input field
	 * @param IRenderHTML $Parent
	 * @return String|void always returns void
	 */
	function renderHTML(IRequest $Request, IAttributes $Attr = null, IRenderHTML $Parent = null) {
		$ClassAttr = null;
		if(static::CSS_CLASS)
			$ClassAttr = new ClassAttributes(static::CSS_CLASS);
		if($this->isOpenTag()) {
			$ContentAttr = null;

			if($Attr && static::PASS_DOWN_ATTRIBUTES) {
				$ContentAttr = $Attr;
				$Attr = null;
			}

			echo RI::ni(), "<", $this->getElementType(), $this->getAttributes()->render($Attr, $ClassAttr), '>';

			if(static::CSS_CONTENT_CLASS !== null) {
				if($ContentAttr) {
					$ContentAttr = new AttributeCollection($ContentAttr, new ClassAttributes(static::CSS_CONTENT_CLASS));
				} else {
					$ContentAttr = new ClassAttributes(static::CSS_CONTENT_CLASS);
				}
			}
			$this->renderContent($Request, $ContentAttr, $Parent);

			echo "</", $this->getElementType(), ">";

		} else {
			echo RI::ni(), "<", $this->getElementType(), $this->getAttributes()->render($Attr, $ClassAttr), "/>";

		}
	}


	/**
	 * Add a log listener callback
	 * @param ILogListener $Listener
	 * @return void
	 */
	function addLogListener(ILogListener $Listener) {
		$this->mLogListeners[] = $Listener;
	}

	protected function getLogListeners() {
		return $this->mLogListeners;
	}

	function __toString() {
		$ClassAttr = null;
		if(static::CSS_CLASS)
			$ClassAttr = new ClassAttributes(static::CSS_CLASS);
		return "<" . $this->getElementType() . $this->getAttributes() . $ClassAttr . '>';
	}


}