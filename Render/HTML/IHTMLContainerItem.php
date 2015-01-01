<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 12/2/14
 * Time: 8:40 PM
 */
namespace CPath\Render\HTML;

interface IHTMLContainerItem
{
	/**
	 * Called when item is added to an IHTMLContainer
	 * @param IHTMLContainer $Parent
	 * @return void
	 */
	function onContentAdded(IHTMLContainer $Parent);

	/**
	 * Return element parent or null
	 * @return IHTMLContainer|null
	 */
	function getParent();
}