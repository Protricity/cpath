<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Handlers\Interfaces;


use CPath\Framework\Render\Interfaces\IRender;

interface IAttributes extends IRender{

    /**
     * Add an attribute to the collection
     * @param String|Null $key the attribute name. If null is provided, the attribute is not added
     * @param String|Null $value the attribute value
     * @param bool $replace should any existing value be replaced
     * @return IAttributes returns self
     * @throws \InvalidArgumentException if $replace == false and the attribute exists
     * or $replace == true and the attribute does not exist
     */
    function add($key=null, $value = null, $replace=false);

    /**
     * Add html to the attribute content
     * @param String|Null $html the attribute html content
     * @return IAttributes returns self
     */
    function addHTML($html=null);

    /**
     * Add a css class to the collection
     * @param String|Null $class one or multiple css classes. If null is provided, the class is not added
     * @return IAttributes returns self
     */
    function addClass($class=null);

    /**
     * Add a css style to the collection
     * @param String|Null $style one or multiple css styles. If null is provided, the style is not added
     * @return IAttributes returns self
     */
    function addStyle($style=null);
}