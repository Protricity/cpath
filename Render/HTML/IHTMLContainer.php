<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 10/19/14
 * Time: 6:27 PM
 */
namespace CPath\Render\HTML;

use CPath\Render\HTML\Attribute\IAttributes;
use CPath\Render\HTML\Header\IHTMLSupportHeaders;
use CPath\Request\IRequest;

interface IHTMLContainer extends IRenderHTML, IHTMLSupportHeaders
{
	/**
	 * Add support headers to content
	 * @param IHTMLSupportHeaders $Headers
	 * @return void
	 */
	function addSupportHeaders(IHTMLSupportHeaders $Headers);

	/**
	 * Returns an array of IRenderHTML content
	 * @param null $key if provided, get content by key
	 * @return IRenderHTML[]
	 * @throws \InvalidArgumentException if content at $key was not found
	 */
	public function getContent($key = null);

	/**
	 * Add IRenderHTML Content
	 * @param IRenderHTML $Render
	 * @param null $key if provided, add/replace content by key
	 * @return void
	 */
	function addContent(IRenderHTML $Render, $key = null);

	/**
	 * Returns true if content is available and should render
	 * @param null $key if provided, returns true if content at this key index exists
	 * @return bool
	 */
	function hasContent($key = null);

	/**
	 * Remove all content or content at a specific key
	 * @param null $key if provided, removes content at key, if exists
	 * @return int the number of items removed
	 */
	function removeContent($key = null);

	/**
	 * Render element content
	 * @param IRequest $Request
	 * @param IAttributes $ContentAttr
	 */
	function renderContent(IRequest $Request, IAttributes $ContentAttr = null);
}


