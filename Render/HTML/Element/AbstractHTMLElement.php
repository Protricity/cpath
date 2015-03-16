<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 10/19/14
 * Time: 10:57 AM
 */
namespace CPath\Render\HTML\Element;

use CPath\Render\Helpers\RenderIndents as RI;
use CPath\Render\HTML\Attribute\Attributes;
use CPath\Render\HTML\Attribute\IAttributes;
use CPath\Render\HTML\Attribute\IAttributesAggregate;
use CPath\Render\HTML\Header\IHeaderWriter;
use CPath\Render\HTML\Header\IHTMLHeaderContainer;
use CPath\Render\HTML\Header\IHTMLSupportHeaders;
use CPath\Render\HTML\HTMLHeaderContainer;
use CPath\Render\HTML\IHTMLContainer;
use CPath\Render\HTML\IRenderHTML;
use CPath\Request\IRequest;
use CPath\Request\Log\ILogListener;
use CPath\Response\IResponse;

abstract class AbstractHTMLElement extends Attributes implements IResponse, IHTMLElement, IHTMLSupportHeaders, IHTMLHeaderContainer
{
	const FLAG_SKIP_INDENTS = 0x01;
	const FLAG_SKIP_NEWLINE = 0x02;

	const PASS_DOWN_ATTRIBUTES = false;

	private $mElmType;

	/** @var ILogListener[] */
	private $mLogListeners = array();

	/** @var HTMLHeaderContainer */
	private $mHeaders = array();

	/** @var IHTMLContainer */
	private $mParent = null;

	private $mFlags = 0;

	/**
	 * @param string $elmType
	 * @param null|String|Array|IAttributes $_attributes [varargs] attribute html as string, array, or IAttributes instance
	 */
	public function __construct($elmType, $_attributes = null) {
		parent::__construct();
		if(strpos($elmType, ' ') !== false) {
			list($elmType, $attrHTML) = explode(' ', $elmType, 2);
			$this->addAttributeHTML($attrHTML);
		}
		$this->mElmType = $elmType;

		for($i=1; $i<func_num_args(); $i++) {
			$arg = func_get_arg($i);
			$this->addVarArg($arg);
		}
	}

	/**
	 * Returns true one or more flags are set, otherwise false
	 * Note: multiple flags follows 'OR' logic. Only one flag has to match to return true
	 * @param int $flag the flag or flags to check
	 * @return bool true one or more flags are set, otherwise false
	 */
	function hasFlag($flag) { return $this->mFlags & $flag ? true : false; }

	/**
	 * Set
	 * @param $flag
	 * @param bool $on
	 * @return $this
	 */
	function setFlag($flag, $on = true) {
		$on
			? $this->mFlags |= $flag
			: $this->mFlags &= ~$flag;
		return $this;
	}

	/**
	 * Get the request status code
	 * @return int
	 */
	function getCode() {
		return IResponse::HTTP_SUCCESS;
	}

	/**
	 * Get the IResponse Message
	 * @return String
	 */
	function getMessage() {
		return $this->getElementType() . " element";
	}

	/**
	 * Return element parent or null
	 * @return IHTMLContainer|null
	 */
	public function getParent() {
		return $this->mParent;
	}

	/**
	 * Called when item is added to an IHTMLContainer
	 * @param IHTMLContainer $Parent
	 * @return void
	 */
	function onContentAdded(IHTMLContainer $Parent) {
		$this->mParent = $Parent;
	}

	protected function addVarArg($arg) {
		if($arg instanceof IHTMLSupportHeaders)
			$this->addSupportHeaders($arg);

		if($arg instanceof IAttributesAggregate)
			$this->addAttributes($arg->getAttributes());

		if($arg instanceof IAttributes)
			$this->addAttributes($arg);

		if(is_string($arg))
			$this->addAttributeHTML($arg);
	}

	/**
	 * Add support headers to content
	 * @param IHTMLSupportHeaders $Headers
	 * @param IHTMLSupportHeaders $_Headers [vararg]
	 * @return void
	 */
	public function addSupportHeaders(IHTMLSupportHeaders $Headers, IHTMLSupportHeaders $_Headers=null) {
		$this->getSupportHeaders();
		foreach(func_get_args() as $Headers)
			$this->getSupportHeaders()->addSupportHeaders($Headers);
	}


	/**
	 * @return HTMLHeaderContainer
	 */
	function getSupportHeaders() {
		return $this->mHeaders ?: $this->mHeaders = new HTMLHeaderContainer();
	}

	/**
	 * Get meta tag content or return null
	 * @param String $name tag name
	 * @return String|null
	 */
	function getMetaTagContent($name) {
		return $this->getSupportHeaders()->getMetaTagContent($name);
	}

	/**
	 * Write all support headers used by this renderer
	 * @param IRequest $Request
	 * @param IHeaderWriter $Head the writer inst to use
	 * @return void
	 */
	function writeHeaders(IRequest $Request, IHeaderWriter $Head) {
        parent::writeHeaders($Request, $Head);
		$this->getSupportHeaders()->writeHeaders($Request, $Head);
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

	/**
	 * Get HTMLElement node type
	 * @return String
	 */
	function getElementType() {
		return $this->mElmType;
	}

	/**
	 * Render HTML element and content
	 * @param IRequest $Request the IRequest inst for this render which contains the request and remaining args
	 * @param IAttributes $Attr optional attributes for the input field
	 * @param IRenderHTML $Parent
	 * @return String|void always returns void
	 */
	function renderHTML(IRequest $Request, IAttributes $Attr = null, IRenderHTML $Parent = null) {
		if($this->isOpenTag()) {
			$ContentAttr = null;

			if($Attr && static::PASS_DOWN_ATTRIBUTES) {
				$ContentAttr = $Attr;
				$Attr = null;
			}
			if(!$this->hasFlag(self::FLAG_SKIP_NEWLINE))
				echo RI::ni();
			echo "<", $this->getElementType(), $this->renderHTMLAttributes($Request), '>';
			$this->renderContent($Request, $ContentAttr, $Parent);
			echo "</", $this->getElementType(), ">";

		} else {
			if(!$this->hasFlag(self::FLAG_SKIP_NEWLINE))
				echo RI::ni();
			echo "<", $this->getElementType(), $this->renderHTMLAttributes($Request), '/>';

		}
	}

	/**
	 * Add a log listener callback
	 * @param ILogListener $Listener
	 * @return void
	 */
	function addLogListener(ILogListener $Listener) {
		if(!in_array($Listener, $this->mLogListeners))
			$this->mLogListeners[] = $Listener;
	}

	protected function getLogListeners() {
		return $this->mLogListeners;
	}

    /**
     * Returns true if attributes match the given selector
     * @param String $selector
     * @return bool
     */
    function matchesSelector($selector) {
        if($this->getElementType() === $selector)
            return true;
        foreach($this->getClasses() as $class) {
            if($selector === '.' . $class)
                return true;
            if($selector === $this->getElementType() . '.' . $class)
                return true;
        }
        return false;
    }

    function __toString() {
		return "<" . $this->getElementType() . $this->getHTMLAttributeString() . ($this->isOpenTag() ? '>' : '/>');
	}
}