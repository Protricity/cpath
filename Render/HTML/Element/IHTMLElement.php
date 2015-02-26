<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 1/6/2015
 * Time: 12:09 PM
 */
namespace CPath\Render\HTML\Element;

use CPath\Render\HTML\IHTMLContainer;
use CPath\Render\HTML\IHTMLContainerItem;
use CPath\Render\HTML\IRenderHTML;

interface IHTMLElement extends IHTMLContainerItem, IRenderHTML
{
	/**
	 * Return element parent or null
	 * @return IHTMLContainer|null
	 */
//	function getParent();

	/**
	 * Get HTMLElement node type
	 * @return String
	 */
	function getElementType();
}