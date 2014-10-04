<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Render\HTML\Attribute;

use CPath\Render\HTML\Attribute;

final class HTMLAttributes implements IAttributes {
    private $mAttr = array();
    /** @var ClassAttributes */
    private $mClasses = null;
    /** @var StyleAttributes */
    private $mStyles = null;

    function __construct($htmlAttr=null) {
        if($htmlAttr)
            $this->addHTML($htmlAttr);
    }


    /**
     * Add an attribute to the collection
     * @param String $attrName the attribute name. If null is provided, the attribute is not added
     * @param String|null $value the attribute value. If null is provided,
     * @throws \InvalidArgumentException if $replace == false and the attribute exists
     * or $replace == true and the attribute does not exist
     */
    function setAttribute($attrName, $value = null) {
        switch(strtolower($attrName)) {
            case 'class':
                $this->addClass($value);
                break;

            case 'style':
                $this->addStyles($value);
                break;

            default:
                $this->mAttr[$attrName] = $value;
        }
    }

    function getAttribute($attrName, $defaultValue=null) {
        return isset($this->mAttr[$attrName]) ? $this->mAttr[$attrName] : $defaultValue;
    }

    function hasAttribute($attrName) {
        return isset($this->mAttr[$attrName]);
    }

    /**
     * Add attributes
     * @param $htmlAttr
     */
    function addHTML($htmlAttr) {
        if(preg_match_all('/([a-z0-9_]+)\s*=\s*[\"\'](.*?)[\"\']/is', $htmlAttr, $matches)) {
            foreach($matches[1] as $name) {
                $this->setAttribute($name, $matches[2]) ;
            }
        }
    }

    /**
     * Checks to see if a class exists in the class list
     * @param String $class
     * @return bool
     */
    function hasClass($class) {
        return $this->mClasses && $this->mClasses->hasClass($class);
    }

    function hasAttr($attrName) {
        return isset($this->mAttr[$attrName]);
    }

    /**
     * Add a css class to the collection
     * @param Array|String $classList one or multiple css classes
     */
    function addClass($classList) {
        if(!$this->mClasses)
            $this->mClasses = new ClassAttributes($classList);
        else
            $this->mClasses->addClass($classList);
    }

    /**
     * Add css styles to the collection
     * @param String $styleList one or multiple css styles
     */
    function addStyles($styleList) {
        if(!$this->mStyles)
            $this->mStyles = new StyleAttributes($styleList);
        else
            $this->mStyles->addStyles($styleList);
    }

    /**
     * Add a css style to the collection
     * @param $name
     * @param $value
     */
    function addStyle($name, $value) {
        if(!$this->mStyles)
            $this->mStyles = new StyleAttributes();
        $this->mStyles->addStyle($name, $value);
    }

    /**
     * Get html attribute string
     * @return String
     */
    function __toString() {
        $attr = '';
        if($this->mClasses)
            $attr .= $this->mClasses;
        if($this->mStyles)
            $attr .= $this->mStyles;
        foreach($this->mAttr as $key => $value)
            $attr .= ' ' . $key . "='" . $value . "'";
        return $attr;
    }

    /**
     * Merge attributes and return an instance
     * @param IAttributes|null $Attributes
     * @return IAttributes
     */
    function merge(IAttributes $Attributes = null) {
        if($Attributes)
            $this->addHTML((String)$Attributes);
        return $this;
    }
}