<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 10/21/14
 * Time: 12:00 AM
 */
namespace CPath\Render\HTML;

use CPath\Framework\Render\Header\IHeaderWriter;
use CPath\Render\HTML\Attribute\IAttributes;
use CPath\Render\HTML\Header\HTMLHeaders;
use CPath\Render\HTML\Header\IHTMLSupportHeaders;
use CPath\Request\IRequest;

class HTMLContainer extends AbstractHTMLContainer
{
	/** @var IRenderHTML[] */
	private $mContent = array();
	/** @var IHTMLContainer */
	private $mItemTemplate = null;


	/**
	 * @param String|null $_content [optional] varargs of content
	 */
	public function __construct($_content = null) {
		$this->addAll(func_get_args());
	}

	public function setItemTemplate(IHTMLContainer $Template) {
		$this->mItemTemplate = $Template;
	}

	/**
	 * Write all support headers used by this IView instance
	 * @param IRequest $Request
	 * @param IHeaderWriter $Head the writer instance to use
	 * @return String|void always returns void
	 */
	function writeHeaders(IRequest $Request, IHeaderWriter $Head) {
		foreach ($this->getContent() as $Content)
			if ($Content instanceof IHTMLSupportHeaders)
				$Content->writeHeaders($Request, $Head);
	}

	public function addHeaders(IHTMLSupportHeaders $Headers) {
		$this->addContent(new HTMLHeaders($Headers));
	}

	/**
	 * Add any kind of content
	 * @param $content
	 * @param null $_content
	 */
	protected function addAll($content, $_content=null) {
		foreach(func_get_args() as $arg) {
			if(is_array($arg)) {
				foreach($arg as $a)
					$this->addAll($a);
			} else {
				$this[] = $arg;
			}
		}
	}

	/**
	 * Add IRenderHTML Content
	 * @param IRenderHTML $Render
	 * @param null $key if provided, add/replace content by key
	 * @return String|void always returns void
	 */
	function addContent(IRenderHTML $Render, $key = null) {
		if ($key !== null)
			$this->mContent[$key] = $Render;
		else
			$this->mContent[] = $Render;
	}

	/**
	 * Returns true if content is available and should render
	 * @param null $key if provided, returns true if content at this key index exists
	 * @return bool
	 */
	function hasContent($key = null) {
		if ($key === null)
			return sizeof($this->mContent) > 0;

		return isset($this->mContent[$key]);
	}

	/**
	 * Returns an array of IRenderHTML content
	 * @param null $key if provided, get content by key
	 * @return IRenderHTML[]|IRenderHTML
	 * @throws \InvalidArgumentException if content at $key was not found
	 */
	public function getContent($key = null) {
		if ($key === null)
			return $this->mContent;

		if (!isset($this->mContent[$key]))
			throw new \InvalidArgumentException("Content at '{$key}'' was not found");

		return $this[$key];
	}


	/**
	 * Remove template content
	 * @param null $key if provided, removes content at key, if exists
	 * @return int the number of items removed
	 */
	function removeContent($key = null) {
		if($key !== null) {
			if(isset($this->mContent[$key])) {
				unset($this->mContent[$key]);
				return 1;
			}
			return 0;
		}

		$c = sizeof($this->mContent);
		$this->mContent = array();
		return $c;
	}

	/**
	 * Render request as html
	 * @param IRequest $Request the IRequest instance for this render which contains the request and remaining args
	 * @param IAttributes $Attr
	 * @return String|void always returns void
	 */
	function renderHTML(IRequest $Request, IAttributes $Attr = null) {
		$this->renderContent($Request);
	}

	/**
	 * Render element content
	 * @param IRequest $Request
	 * @param IAttributes $ContentAttr
	 */
	function renderContent(IRequest $Request, IAttributes $ContentAttr = null) {
		foreach($this->getContent() as $ContentItem) {
			$this->renderContentItem($Request, $ContentItem, $ContentAttr);
		}
	}

	protected function renderContentItem(IRequest $Request, IRenderHTML $Content, IAttributes $ContentAttr = null) {
		if($Content instanceof HTMLContainer) {
			$this->renderContent($Request, $ContentAttr);
		} else {
			$Content->renderHTML($Request, $ContentAttr);
		}
	}
}