<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Render\HTML\Attribute;

interface IAttributes {

	/**
	 * Returns an array of classes
	 * @return Array
	 */
	function getClasses();

	/**
	 * Return the style value or a name-value associative array
	 * @param null $name
	 * @return String|null|Array
	 */
	function getStyle($name=null);

	/**
	 * Return the attribute value or a name-value associative array
	 * @param null $name
	 * @return String|null|Array
	 */
	function getAttribute($name=null);

	/**
	 * Render html attributes
	 * @param IAttributes|null $Additional
	 * @return string|void always returns void
	 */
	function render(IAttributes $Additional=null);

	/**
	 * Get html attribute string
	 * @return String
	 */
	function __toString();
}