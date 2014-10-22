<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 10/21/14
 * Time: 11:41 PM
 */
namespace CPath\Render\HTML\Template;

use CPath\Render\HTML\IHTMLContainer;
use CPath\Request\IRequest;

interface IHTMLTemplate extends IHTMLContainer
{
	/**
	 * Render request as html
	 * @param IRequest $Request the IRequest instance for this render which contains the request and remaining args
	 * @return String|void always returns void
	 */
	function renderHTMLTemplate(IRequest $Request);
}