<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 12/2/14
 * Time: 5:29 PM
 */
namespace CPath\Response\Common;

use CPath\Data\Map\IKeyMap;
use CPath\Data\Map\ISequenceMap;
use CPath\Render\HTML\Attribute\IAttributes;
use CPath\Render\HTML\Common\ObjectRenderer;
use CPath\Render\HTML\Header\IHeaderWriter;
use CPath\Render\HTML\Header\IHTMLSupportHeaders;
use CPath\Render\HTML\HTMLContainer;
use CPath\Render\HTML\IHTMLContainer;
use CPath\Render\HTML\IRenderHTML;
use CPath\Request\IRequest;
use CPath\Response\Response;

class RenderableResponse extends Response implements IHTMLContainer
{
	private $mContainer = null;

	/**
	 * Create a new response
	 * @param String $message the response message
	 * @param int|bool $status the response status code or true/false for success/error
	 * @param null $_content
	 */
	function __construct($message=NULL, $status=true, $_content=null) {
		$this->mContainer = new HTMLContainer();
		$args = func_get_args();
		foreach($args as $arg)
			if($arg instanceof IRenderHTML)
				$this->mContainer->addContent($arg);
			elseif($arg instanceof IKeyMap)
				$this->mContainer->addContent(new ObjectRenderer($arg));
			elseif($arg instanceof ISequenceMap)
				$this->mContainer->addContent(new ObjectRenderer($arg));
			else if (is_string($arg))
				$this->setMessage($arg);
			else if (is_bool($arg) || is_int($arg))
				$this->setStatusCode($arg);
	}


	/**
	 * Add support headers to content
	 * @param IHTMLSupportHeaders $Headers
	 * @return void
	 */
	function addSupportHeaders(IHTMLSupportHeaders $Headers) {
		$this->mContainer->addSupportHeaders($Headers);
	}

	/**
	 * Returns an array of IRenderHTML content
	 * @param null $key if provided, get content by key
	 * @return IRenderHTML[]
	 * @throws \InvalidArgumentException if content at $key was not found
	 */
	public function getContent($key = null) {
		return $this->mContainer->getContent($key);
	}

	/**
	 * Add IRenderHTML MainContent
	 * @param IRenderHTML $Render
	 * @param null $key if provided, add/replace content by key
	 * @return void
	 */
	function addContent(IRenderHTML $Render, $key = null) {
		$this->mContainer->addContent($Render, $key);
	}

	/**
	 * Returns true if content is available and should render
	 * @param null $key if provided, returns true if content at this key index exists
	 * @return bool
	 */
	function hasContent($key = null) {
		return $this->mContainer->hasContent($key);
	}

	/**
	 * Remove all content or content at a specific key
	 * @param null $key if provided, removes content at key, if exists
	 * @return int the number of items removed
	 */
	function removeContent($key = null) {
		return $this->mContainer->removeContent($key);
	}

	/**
	 * Render element content
	 * @param IRequest $Request
	 * @param IAttributes $ContentAttr
	 */
	function renderContent(IRequest $Request, IAttributes $ContentAttr = null) {
		$this->mContainer->removeContent($Request, $ContentAttr);
	}

	/**
	 * Write all support headers used by this renderer
	 * @param IRequest $Request
	 * @param IHeaderWriter $Head the writer inst to use
	 * @return void
	 */
	function writeHeaders(IRequest $Request, IHeaderWriter $Head) {
		$this->mContainer->writeHeaders($Request, $Head);
	}

	/**
	 * Render request as html
	 * @param IRequest $Request the IRequest inst for this render which contains the request and remaining args
	 * @param IAttributes $Attr
	 * @param IRenderHTML $Parent
	 * @return String|void always returns void
	 */
	function renderHTML(IRequest $Request, IAttributes $Attr = null, IRenderHTML $Parent = null) {
		$this->mContainer->renderHTML($Request, $Attr, $Parent);
	}

}