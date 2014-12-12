<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 12/6/14
 * Time: 11:47 PM
 */
namespace CPath\Render\HTML\Attribute;

use CPath\Request\IRequest;

interface IClassAttributes {

	/**
	 * Get an array of classes
	 * @return Array
	 */
	function getClasses();

	/**
	 * Return an associative array of attribute name-value pairs
	 * @param \CPath\Request\IRequest $Request
	 * @return string
	 */
	function getHTMLClassAttributeString(IRequest $Request=null);

	/**
	 * Render class attribute
	 * @param IRequest $Request
	 * @internal param \CPath\Render\HTML\Attribute\IAttributes|null $Additional
	 * @return string|void always returns void
	 */
	function renderHTMLClassAttributeValue(IRequest $Request=null);
}