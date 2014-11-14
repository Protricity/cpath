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
     * Add an attribute to the collection
     * @param String $key the attribute name. If null is provided, the attribute is not added
     * @param String|null $value the attribute value
     * @throws \InvalidArgumentException if $replace == false and the attribute exists
     * or $replace == true and the attribute does not exist
     */
    //function add($key, $value = null);

    /**
     * Add a css class to the collection
     * @param String $class one or multiple css classes. If null is provided, the class is not added
     */
    //function addClass($class);

    /**
     * Checks to see if a class exists in the class list
     * @param $class
     * @return bool
     */
    //function hasClass($class);

	/**
	 * Returns an array of classes
	 * @return Array
	 */
	function getClasses();

	/**
	 * Return the style value or a name-value associative array
	 * @param null $name
	 * @return String|Array
	 */
	function getStyle($name=null);

	/**
	 * Return the attribute value or a name-value associative array
	 * @param null $name
	 * @return String|Array
	 */
	function getAttribute($name=null);

	/**
	 * Render html attributes
	 * @param IAttributes|null $Additional
	 * @return string|void always returns void
	 */
	function render(IAttributes $Additional=null);

    /**
     * Merge attributes and return an instance
     * @param IAttributes|null $Attributes
     * @return IAttributes
     */
    //function merge(IAttributes $Attributes=null);

    /**
     * Add a css style to the collection
     * @param String $style one or multiple css styles. If null is provided, the style is not added
     */
    //function addStyle($style);


    /**
     * Get html attribute string
     * @return String
     */
    //function __toString();
}