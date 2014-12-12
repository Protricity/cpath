<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 12/6/14
 * Time: 11:47 PM
 */
namespace CPath\Render\HTML\Attribute;

use CPath\Request\IRequest;

interface IStyleAttributes {

	/**
	 * Get an associative array of stylesheet values
	 * @return Array
	 */
	function getStyleSheetList();


	/**
	 * Return an associative array of attribute name-value pairs
	 * @param \CPath\Request\IRequest $Request
	 * @return string
	 */
	function getHTMLStyleAttributeString(IRequest $Request=null);

	/**
	 * Render style attribute
	 * @param IRequest $Request
	 * @return string|void always returns void
	 */
	function renderHTMLStyleAttributeValue(IRequest $Request=null);
}