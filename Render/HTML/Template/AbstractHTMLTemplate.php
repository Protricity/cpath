<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 11/13/14
 * Time: 10:15 PM
 */
namespace CPath\Render\HTML\Template;

use CPath\Render\HTML\Attribute\IAttributes;
use CPath\Render\HTML\Header\IHTMLSupportHeaders;
use CPath\Render\HTML\IHTMLContainer;
use CPath\Render\HTML\IRenderHTML;
use CPath\Request\IRequest;

abstract class AbstractHTMLTemplate implements IHTMLContainer
{

	/** @var IHTMLSupportHeaders[] */
	private $mSupportHeaders = array();

	/**
	 * Get the container element for this template
	 * @return IHTMLContainer
	 */
	abstract function getContainer();

	/**
	 * Get the top renderable element for this template
	 * @return IHTMLContainer
	 */
	abstract function getRenderer();


	/**
	 * Add support headers to content
	 * @param IHTMLSupportHeaders $Headers
	 * @return void
	 */
	public function addSupportHeaders(IHTMLSupportHeaders $Headers) {
		$this->mSupportHeaders[] = $Headers;
	}

	/**
	 * Add any kind of content
	 * @param \CPath\Render\HTML\IRenderHTML $content
	 * @param \CPath\Render\HTML\IRenderHTML|null $_content
	 */
	function addAll(IRenderHTML $content, IRenderHTML $_content=null) {
		foreach(func_get_args() as $arg) {
			$this->getContainer()->addContent($arg);
		}
	}

	/**
	 * Returns an array of IRenderHTML content
	 * @param null $key if provided, get content by key
	 * @return IRenderHTML[]
	 * @throws \InvalidArgumentException if content at $key was not found
	 */
	public function getContent($key = null) {
		$Container = $this->getContainer();
		return $Container->getContent($key);
	}

	/**
	 * Add IRenderHTML Content
	 * @param IRenderHTML $Render
	 * @param null $key if provided, add/replace content by key
	 * @return void
	 */
	function addContent(IRenderHTML $Render, $key = null) {
		$Container = $this->getContainer();
		$Container->addContent($Render, $key);
	}

	/**
	 * Returns true if content is available and should render
	 * @param null $key if provided, returns true if content at this key index exists
	 * @return bool
	 */
	function hasContent($key = null) {
		$Container = $this->getContainer();
		return $Container->hasContent($key);
	}

	/**
	 * Remove all content or content at a specific key
	 * @param null $key if provided, removes content at key, if exists
	 * @return int the number of items removed
	 */
	function removeContent($key = null) {
		$Container = $this->getContainer();
		return $Container->removeContent($key);
	}

	/**
	 * Render element content
	 * @param IRequest $Request
	 * @param IAttributes $ContentAttr
	 */
	function renderContent(IRequest $Request, IAttributes $ContentAttr = null) {
		$Container = $this->getContainer();
		$Container->renderContent($Request, $ContentAttr);
	}

	/**
	 * Render request as html
	 * @param IRequest $Request the IRequest inst for this render which contains the request and remaining args
	 * @param IAttributes $Attr
	 * @param IRenderHTML $Parent
	 * @return String|void always returns void
	 */
	function renderHTML(IRequest $Request, IAttributes $Attr = null, IRenderHTML $Parent = null) {
		$Renderer = $this->getRenderer();
		if($Renderer instanceof IHTMLContainer) {
			$Renderer->addSupportHeaders($this);
			foreach($this->mSupportHeaders as $Headers)
				$Renderer->addSupportHeaders($Headers);
		}
		$Renderer->renderHTML($Request, $Attr, $Parent);
	}
}