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
use CPath\Render\HTML\Element\IHTMLElement;
use CPath\Render\HTML\Header\HTMLHeaderScript;
use CPath\Render\HTML\Header\HTMLHeaderStyleSheet;
use CPath\Render\HTML\Header\HTMLMetaTag;
use CPath\Render\HTML\Header\IHeaderWriter;
use CPath\Render\HTML\Header\IHTMLSupportHeaders;
use CPath\Request\IRequest;

class HTMLContainer extends AbstractHTMLContainer
{
	const CSS_CONTENT_CLASS = null;
    const DEFAULT_CONTAINER_KEY = '#default';

    /** @var IHTMLContainer[] */
	private $mContainers = array();

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

	public function setContainer(IHTMLContainer $Container, $key = self::DEFAULT_CONTAINER_KEY) {
        $key ?: $key = self::DEFAULT_CONTAINER_KEY;
        foreach($this->getContentRecursive() as $Content) {
            if($Container === $Content) {
                $this->mContainers[$key] = $Content;
                return;
            }
        }
        throw new \InvalidArgumentException("Container not found: " . get_class($Container));
	}

	public function addMetaTag($name, $content) {
		$MetaTag = new HTMLMetaTag($name, $content);
		$this->addSupportHeaders($MetaTag);
		return $MetaTag;
	}

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
     * Get a named container by key name
     * @param $key
     * @return IHTMLContainer
     */
    public function getContainer($key=null) {
        if(isset($this->mContainers[$key]))
            return $this->mContainers[$key];
        if($key === self::DEFAULT_CONTAINER_KEY)
            return $this;
        throw new \InvalidArgumentException("Named Container '$key' is not set");
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
     * @param string $key if provided, passes content to a named container
     * @return void
     */
	function addContent(IRenderHTML $Render, $key = null) {
        $key ?: $key = self::DEFAULT_CONTAINER_KEY;
        if(isset($this->mContainers[$key])) {
            $this->getContainer($key)->addContent($Render, $key);
			return;
		}

//		if ($key !== null) {
//			$this->mContent[$key] = $Render;
//		} else {
			$this->mContent[] = $Render;
//		}
		if($Render instanceof IHTMLContainerItem)
			$Render->onContentAdded($this);
	}

    /**
     * Returns true if content is available and should render
     * @param null|string $key if provided, returns true if content at this key index exists
     * @return bool
     */
	function hasContent($key = null) {
        $key ?: $key = self::DEFAULT_CONTAINER_KEY;
        if(isset($this->mContainers[$key]))
			return $this->getContainer($key)->hasContent($key);

		if ($key === null)
			return sizeof($this->mContent) > 0;

		return isset($this->mContent[$key]);
	}

    /**
     * Returns an array of IRenderHTML content
     * @param null|string $key if provided, get content by key
     * @return IRenderHTML[]|IRenderHTML
     */
	public function getContent($key = null) {
        $key ?: $key = self::DEFAULT_CONTAINER_KEY;
        if(isset($this->mContainers[$key]))
            return $this->getContainer($key)->getContent();

		if ($key === self::DEFAULT_CONTAINER_KEY)
			return $this->mContent;

		if (!isset($this->mContent[$key]))
			throw new \InvalidArgumentException("Content at '{$key}'' was not found");

		return $this[$key];
	}

	public function getContentRecursive(IHTMLContainer $Container=null) {
		if($Container)
			$ContentList = $Container->getContent();
        else
            $ContentList = $this->mContent;
		$array = array();

		foreach($ContentList as $Content) {
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
     * @param null|string $key if provided, removes content at key, if exists
     * @return int the number of items removed
     */
	function removeContent($key = null) {
        $key ?: $key = self::DEFAULT_CONTAINER_KEY;
        if(isset($this->mContainers[$key]))
			return $this->mContainers[$key]->removeContent($key);

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
		$this->renderContent($Request, $ContentAttr, $Parent);
	}

    /**
     * Render element content
     * @param IRequest $Request
     * @param IAttributes $ContentAttr
     * @param IHTMLContainer|IRenderHTML $Parent
     */
	function renderContent(IRequest $Request, IAttributes $ContentAttr = null, IRenderHTML $Parent = null) {
		foreach($this->mContent as $ContentItem) {
			$this->renderContentItem($Request, $ContentItem, $ContentAttr, $Parent);
		}
	}

	protected function renderContentItem(IRequest $Request, IRenderHTML $Content, IAttributes $ContentAttr = null, IRenderHTML $Parent = null) {
		$Content->renderHTML($Request, $ContentAttr, $Parent);
	}

    // Static

	protected static function toString(IHTMLContainer $Container) {
		return get_class($Container);
	}
}