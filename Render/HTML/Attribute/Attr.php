<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Render\HTML\Attribute;

use CPath\Render\HTML\Attribute\IAttributes;
use CPath\Render\HTML\Attribute;
use CPath\Request\IRequest;

class Attr implements IAttributes {
    private $mContent = array();

    function __construct($htmlAttr=null) {
        if($htmlAttr)
            $this->addHTML($htmlAttr);
    }

    /**
     * Render request as html
     * @return String|void always returns void
     */
    function render() {
        foreach($this->mContent as $key => $value) {
            switch($key) {
                case 'class':
                    echo ' class=\'' . implode(' ', array_keys($value)) . '\'';
                    break;
                case 'style':
                    foreach($value as $key2 => $value2)
                        echo " ", $key2, ": ", $value2, ";";
                    break;
                default:
                    echo " ", $key, "='", $value, "'";
            }
        }
    }

    /**
     * Add an attribute to the collection
     * @param String $key the attribute name. If null is provided, the attribute is not added
     * @param String|null $value the attribute value. If null is provided,
     * @throws \InvalidArgumentException if $replace == false and the attribute exists
     * or $replace == true and the attribute does not exist
     */
    function add($key, $value = null) {
        switch(strtolower($key)) {
            case 'class':
                $this->addClass($value);
                break;

            case 'style':
                $this->addStyle($value);
                break;

            default:
                $this->mContent[$key] = $value;
        }
    }

    /**
     * Add attributes
     * @param $htmlAttr
     */
    function addHTML($htmlAttr) {
        if(preg_match_all('/([a-z0-9_]+)\s*=\s*[\"\'](.*?)[\"\']/is', $htmlAttr, $matches)) {
            foreach($matches[1] as $name) {
                $this->add($name, $matches[2]) ;
            }
        }
    }

    /**
     * Add a css class to the collection
     * @param String $classList one or multiple css classes
     */
    function addClass($classList) {
        foreach(preg_split('/\s+/', $classList) as $class)
            $this->mContent['class'][$class] = true;
    }

    /**
     * Add a css style to the collection
     * @param String $styleList one or multiple css styles
     */
    function addStyle($styleList) {
        if(!isset($this->mContent['style']))
            $this->mContent['style'] = array();
        if(preg_match_all('/(\w+):\s+([\w\s,]+);?/', $styleList, $matches)) {
            foreach($matches[1] as $name) {
                $this->mContent['style'][$name] = $matches[2][$name];
            }
        }
    }

    // Static

    /**
     * Parse attributes from string and return the class instance
     * @param $attrString|IAttributes
     * @param null|IAttributes|String $_htmlAttr [vararg]
     * @return \CPath\Render\HTML\Attribute\IAttributes
     */
    static function parse($htmlAttr, $_htmlAttr=null) {
        $Attr = new Attr();
        foreach(func_get_args() as $arg)
            if($arg)
                $Attr->addHTML($arg);
        return $Attr;
    }

    /**
     * Add a css class to the collection
     * @param String $classList one or multiple css classes
     * @param null|String $_classList [vararg]
     * @return \CPath\Render\HTML\Attribute\IAttributes
     */
    static function fromClass($classList, $_classList=null) {
        $Attr = new Attr();
        foreach(func_get_args() as $arg)
            if($arg)
                $Attr->addClass($arg);
        return $Attr;
    }

    /**
     * Add a css style to the collection
     * @param String $styleList one or multiple css styles
     * @param null|String $_styleList [vararg]
     * @return \CPath\Render\HTML\Attribute\IAttributes
     */
    static function fromStyle($styleList, $_styleList=null) {
        $Attr = new Attr();
        foreach(func_get_args() as $arg)
            if($arg)
                $Attr->addStyle($arg);
        return $Attr;
    }

}