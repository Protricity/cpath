<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 12/3/14
 * Time: 5:34 PM
 */
namespace CPath\Render\HTML;

use CPath\Render\HTML\Header\IHTMLSupportHeaders;

interface IHTMLElement extends IRenderHTML, IHTMLSupportHeaders, IHTMLContainerItem
{
	/**
	 * Return element parent or null
	 * @return IHTMLContainer|null
	 */
	function getParent();
}