<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 10/21/14
 * Time: 12:00 AM
 */
namespace CPath\Render\HTML;

use CPath\Render\HTML\Attribute\ClassAttributes;
use CPath\Render\HTML\Attribute\IAttributes;
use CPath\Render\HTML\Header\HTMLHeaderScript;
use CPath\Render\HTML\Header\HTMLHeaderStyleSheet;
use CPath\Render\HTML\Header\HTMLMetaTag;
use CPath\Render\HTML\Header\IHeaderWriter;
use CPath\Render\HTML\Header\IHTMLSupportHeaders;
use CPath\Request\IRequest;

class HTMLContainer extends AbstractHTMLContainer
{
	const CSS_CONTENT_CLASS = null;

	/** @var HTMLContainer */
	private $mTargetContainer = null;

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

	public function setContainer(IHTMLContainer $Container) {
		$Contents = $this->getContentRecursive();
		if(!in_array($Container, $Contents, true))
			throw new \InvalidArgumentException("Container not found recursively: " . static::toString($Container));
		$this->mTargetContainer = $Container;
	}

	public function addMetaTag($name, $content) {
		$MetaTag = new HTMLMetaTag($name, $content);
		$this->addSupportHeaders($MetaTag);
		return $MetaTag;
	}
//
//	public function getMetaTagContent($name) {
//		foreach($this->getSupportHeaders() as $Header) {
//			if($Header instanceof HTMLMetaTag) {
//				if($Header->getName() === $name) {
//					return $Header->getContent();
//				}
//			}
//		}
//
//		foreach($this->getContent() as $Content)
//			if($Content instanceof IHTMLHeaderContainer)
//				if($content = $Content->getMetaTagContent($name))
//					return $content;
//
//		return null;
//	}

	public function addHeaderScript($path, $defer = false, $charset = null) {
		$this->addSupportHeaders(new HTMLHeaderScript($path, $defer, $charset));
	}

	public function addHeaderStyleSheet($path) {
		$this->addSupportHeaders(new HTMLHeaderStyleSheet($path));
	}

	public function setItemTemplate(IHTMLContainer $Template) {
		$this->mItemTemplate = $Template;
	}

	/**
	 * Write all support headers used by this IView inst
	 * @param IRequest $Request
	 * @param \CPath\Render\HTML\Header\IHeaderWriter $Head the writer inst to use
	 * @return void
	 */
	function writeHeaders(IRequest $Request, IHeaderWriter $Head) {
		parent::writeHeaders($Request, $Head);
		foreach ($this->getContent() as $Content)
			if ($Content instanceof IHTMLSupportHeaders)
				$Content->writeHeaders($Request, $Head);
	}

	/**
	 * Add any kind of content
	 * @param $content
	 * @param null $_content
	 */
	public function addAll($content, $_content=null) {
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
	 * Add IRenderHTML MainContent
	 * @param IRenderHTML $Render
	 * @param null $key if provided, add/replace content by key
	 * @return void
	 */
	function addContent(IRenderHTML $Render, $key = null) {
		if($this->mTargetContainer) {
			$this->mTargetContainer->addContent($Render, $key);
			return;
		}

		if ($key !== null) {
			$this->mContent[$key] = $Render;
		} else {
			$this->mContent[] = $Render;
		}
		if($Render instanceof IHTMLContainerItem)
			$Render->onContentAdded($this);
	}

	/**
	 * Add IRenderHTML MainContent
	 * @param IRenderHTML $Render
	 * @param null $key if provided, add/replace content by key
	 * @return void
	 */
	function prependContent(IRenderHTML $Render, $key = null) {
		if($this->mTargetContainer)
			$this->mTargetContainer->prependContent($Render);
		else if ($key !== null)
			$this->mContent = array($key => $Render) + $this->mContent;
		else
			array_unshift($this->mContent, $Render);
		if($Render instanceof IHTMLContainerItem)
			$Render->onContentAdded($this);
	}

	/**
	 * Returns true if content is available and should render
	 * @param null $key if provided, returns true if content at this key index exists
	 * @return bool
	 */
	function hasContent($key = null) {
		if($this->mTargetContainer)
			return $this->mTargetContainer->hasContent($key);

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
		if($this->mTargetContainer)
			return $this->mTargetContainer->getContent($key);

		if ($key === null)
			return $this->mContent;

		if (!isset($this->mContent[$key]))
			throw new \InvalidArgumentException("Content at '{$key}'' was not found");

		return $this[$key];
	}

	public function getContentRecursive(IHTMLContainer $Container=null) {
		if(!$Container)
			$Container = $this; // $this->mTargetContainer ?:
		$array = array();

		foreach($Container->getContent() as $Content) {
			$array[] = $Content;
			if($Content instanceof IHTMLContainer) {
				foreach($this->getContentRecursive($Content) as $C)
					$array[] = $C;
			}
		}

		return $array;
	}

	/**
	 * Remove template content
	 * @param null $key if provided, removes content at key, if exists
	 * @return int the number of items removed
	 */
	function removeContent($key = null) {
		if($this->mTargetContainer)
			return $this->mTargetContainer->removeContent($key);

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
	 * @param IRequest $Request the IRequest inst for this render which contains the request and remaining args
	 * @param IAttributes $Attr
	 * @param IRenderHTML $Parent
	 * @return String|void always returns void
	 */
	function renderHTML(IRequest $Request, IAttributes $Attr = null, IRenderHTML $Parent = null) {
		$ContentAttr = null;
		if(static::CSS_CONTENT_CLASS !== null)
			$ContentAttr = new ClassAttributes(static::CSS_CONTENT_CLASS);
		$this->renderContent($Request, $ContentAttr);
	}

	/**
	 * Render element content
	 * @param IRequest $Request
	 * @param IAttributes $ContentAttr
	 * @param \CPath\Render\HTML\IHTMLContainer $Parent
	 */
	function renderContent(IRequest $Request, IAttributes $ContentAttr = null, IHTMLContainer $Parent = null) {
		foreach($this->mContent as $ContentItem) {
			$this->renderContentItem($Request, $ContentItem, $ContentAttr);
		}
	}

	protected function renderContentItem(IRequest $Request, IRenderHTML $Content, IAttributes $ContentAttr = null) {
		$Content->renderHTML($Request, $ContentAttr, $this);
	}

	// Static

	protected static function toString(IHTMLContainer $Container) {
		return get_class($Container);
	}
}