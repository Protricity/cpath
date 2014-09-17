<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Render\HTML\Attribute;

use CPath\Render\HTML\IRenderHTML;
use CPath\Request\IRequest;

interface IAttributes {

    /**
     * Add an attribute to the collection
     * @param String $key the attribute name. If null is provided, the attribute is not added
     * @param String|null $value the attribute value
     * @throws \InvalidArgumentException if $replace == false and the attribute exists
     * or $replace == true and the attribute does not exist
     */
    function add($key, $value = null);

    /**
     * Add a css class to the collection
     * @param String $class one or multiple css classes. If null is provided, the class is not added
     */
    function addClass($class);

    /**
     * Add a css style to the collection
     * @param String $style one or multiple css styles. If null is provided, the style is not added
     */
    function addStyle($style);

    /**
     * Render html attributes
     * @return String|void always returns void
     */
    function render();
}