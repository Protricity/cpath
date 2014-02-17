<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Framework\Render\Util;

use CPath\Framework\Request\Interfaces\IRequest;
use CPath\Framework\Render\Interfaces\IAttributes;

class Attr implements IAttributes {

    private $mHTML='', $mAttr = array(), $mClasses = array(), $mStyles = array();

    function __construct($classes=null, $styles=null, $html=null) {
        $this->addClass($classes);
        $this->addStyle($styles);
        $this->addHTML($html);
    }

    /**
     * Render this handler
     * @param IRequest $Request the IRequest instance for this render
     * @return void|String always returns null
     */
    function render(IRequest $Request) {
        if($this->mHTML)
            echo ' ', $this->mHTML;
        foreach($this->mAttr as $name => $value)
            echo " ", $name, "='", $value, "'";
        if($this->mClasses)
            echo ' class=\'' . implode(' ', $this->mClasses) . '\'';
        if($this->mStyles)
            echo ' style=\'' . implode('; ', $this->mStyles) . '\'';
    }

    /**
     * Add an attribute to the collection
     * @param String|Null $key the attribute name. If null is provided, the attribute is not added
     * @param String|Null $value the attribute value. If null is provided,
     * @param bool $replace should any existing value be replaced
     * @return IAttributes returns self
     * @throws \InvalidArgumentException if $replace == false and the attribute exists
     * or $replace == true and the attribute does not exist
     */
    function add($key=null, $value = null, $replace=false) {
        if($key == null)
            return $this;
        if(isset($this->mAttr[$key])) {
            if(!$replace)
                throw new \InvalidArgumentException("Attribute '$key' already exists");
        } else {
            if($replace)
                throw new \InvalidArgumentException("Attribute '$key' does not exist");
        }
        $this->mAttr[$key] = $value;
        return $this;
    }

    /**
     * Returns true if the attribute element name exists
     * @param $key
     * @return mixed
     */
    function has($key) {
        return !empty($this->mAttr[$key]);
    }

    /**
     * Add html to the attribute content
     * @param String|Null $html the attribute html content
     * @return IAttributes returns self
     */
    function addHTML($html=null) {
        $this->mHTML .= ($this->mHTML ? ' ' : '') . $html;
    }

    /**
     * Add a css class to the collection
     * @param String|Null $class one or multiple css classes. If null is provided, the class is not added
     * @return IAttributes returns self
     */
    function addClass($class=null) {
        if($class)
            $this->mClasses[] = $class;
        return $this;
    }

    /**
     * Add a css style to the collection
     * @param String|Null $style one or multiple css styles. If null is provided, the style is not added
     * @return IAttributes returns self
     */
    function addStyle($style=null) {
        if($style)
            $this->mStyles[] = $style;
        return $this;
    }

    // Static

    /**
     * @param null|String|IAttributes $class a css class or instance of IAttributes
     * @return IAttributes
     */
    static function get($class=null) {
        if($class instanceof IAttributes)
            return $class;
        return new Attr($class);
    }

    /**
     * @param String|null $style
     * @return Attr
     */
    static function style($style=null) {
        return new Attr(null, $style);
    }
}