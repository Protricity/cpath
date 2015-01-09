<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 1/6/2015
 * Time: 12:09 PM
 */
namespace CPath\Render\HTML\Element;

use CPath\Render\HTML\Attribute\IAttributes;
use CPath\Render\HTML\IHTMLContainer;
use CPath\Render\HTML\IRenderHTML;
use CPath\Response\IResponse;

interface IHTMLElement extends IAttributes, IResponse, IRenderHTML
{
	/**
	 * Return element parent or null
	 * @return IHTMLContainer|null
	 */
	function getParent();

	/**
	 * Get HTMLElement node type
	 * @return String
	 */
	function getElementType();
}