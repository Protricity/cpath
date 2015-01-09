<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Render\HTML\Attribute;

use CPath\Request\IRequest;

interface IAttributes {
	/**
	 * Return the attribute value
	 * @param String $name
	 * @return String|null
	 */
	function getAttribute($name);

	/**
	 * Render or returns html attributes
	 * @param IRequest $Request
	 * @return void
	 */
	function renderHTMLAttributes(IRequest $Request=null);

	/**
	 * Return an associative array of attribute name-value pairs
	 * @param \CPath\Request\IRequest $Request
	 * @return string
	 */
	function getHTMLAttributeString(IRequest $Request=null);
}