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
use CPath\Render\HTML\Header\IHTMLSupportHeaders;
use CPath\Render\HTML\IHTMLContainer;
use CPath\Render\HTML\IHTMLElement;
use CPath\Render\HTML\IRenderHTML;
use CPath\Request\IRequest;
use CPath\Request\Log\ILogListener;

abstract class AbstractHTMLElement extends Attributes implements IHTMLElement
{
	const PASS_DOWN_ATTRIBUTES = false;

	private $mElmType;

	/** @var ILogListener[] */
	private $mLogListeners = array();

	/** @var IHTMLSupportHeaders[] */
	private $mSupportHeaders = array();

	/** @var IHTMLContainer */
	private $mParent = null;

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
	 * Return element parent or null
	 * @return IHTMLContainer|null
	 */
	public function getParent() {
		return $this->mParent;
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
		foreach(func_get_args() as $Headers) {
			$this->mSupportHeaders[] = $Headers;
		}
	}

	/**
	 * Write all support headers used by this renderer
	 * @param IRequest $Request
	 * @param IHeaderWriter $Head the writer inst to use
	 * @return void
	 */
	function writeHeaders(IRequest $Request, IHeaderWriter $Head) {
		foreach($this->mSupportHeaders as $Headers)
			$Headers->writeHeaders($Request, $Head);
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

			echo RI::ni(), "<", $this->getElementType(), $this->renderHTMLAttributes($Request), '>';
			$this->renderContent($Request, $ContentAttr, $Parent);
			echo "</", $this->getElementType(), ">";

		} else {
			echo RI::ni(), "<", $this->getElementType(), $this->renderHTMLAttributes($Request), '/>';

		}
	}

	/**
	 * Called when item is added to an IHTMLContainer
	 * @param IHTMLContainer $Parent
	 * @return void
	 */
	function onContentAdded(IHTMLContainer $Parent) {
		$this->mParent = $Parent;
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
		return "<" . $this->getElementType() . $this->getHTMLAttributeString() . ($this->isOpenTag() ? '>' : '/>');
	}
}