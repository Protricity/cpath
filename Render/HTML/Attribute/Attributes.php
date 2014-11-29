<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Render\HTML\Attribute;

use CPath\Render\HTML\Attribute;

class Attributes implements IAttributes {
    private $mAttr = array();
    private $mClasses = array();
    private $mStyles = array();
	/** @var IAttributes[] */
	private $mAdditionalAttributes=array();

    function __construct($attrName=null, $attrValue=null, $_attrName=null, $_attrValue=null) {
	    $args = func_get_args();
	    for($i=0; $i<sizeof($args); $i+=2) {
		    $attrName = $args[$i];
		    if(isset($args[$i+1])) {
			    $attrValue = $args[$i+1];
			    $this->setAttribute($attrName, $attrValue);
		    } else {
			    if(!is_array($attrName))
				    $attrName = array($attrName);
			    foreach($attrName as $k=>$v) {
				    if(is_int($k))
					    $this->addHTML($v);
				    else
					    $this->setAttribute($k, $v);
			    }
		    }
	    }
    }

	function addAttributes(IAttributes $Attributes) {
		if($Attributes)
			$this->mAdditionalAttributes[] = $Attributes;
	}

//	protected function getAttributeList() {
//		$arr = array($this);
//		if($this->mAdditionalAttributes)
//			foreach($this->mAdditionalAttributes as $Additional)
//				$arr[] = $Additional;
//		return $arr;
//	}

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

	function hasAttribute($attrName) {
		return isset($this->mAttr[$attrName]);
	}

	function removeAttribute($attrName) {
		if(!$this->hasAttr($attrName))
			return false;
		unset($this->mAttr[$attrName]);
		return true;
	}

    /**
     * Add attributes
     * @param $htmlAttr
     */
    function addHTML($htmlAttr) {
        if(preg_match_all('/([a-z0-9_]+)\s*=\s*[\"\'](.*?)[\"\']/is', $htmlAttr, $matches)) {
            foreach($matches[1] as $i => $name) {
                $this->setAttribute($name, $matches[2][$i]) ;
            }
        } else {
	        $this->addClass($htmlAttr);
	        //throw new \InvalidArgumentException("Invalid element html: " . $htmlAttr);
        }
    }

	/**
	 * Return the attribute value or a name-value associative array
	 * @param null $name
	 * @return String|Array
	 */
	function getAttribute($name = null) {
		$attributes = $this->mAttr;
		foreach($this->mAdditionalAttributes as $Additional)
			$attributes += $Additional->getAttribute();

		if($name === null)
			return $attributes;
		return isset($attributes[$name]) ? $attributes[$name] : null;
	}

	/**
	 * Returns an array of classes
	 * @return Array
	 */
	function getClasses() {
		$classes = $this->mClasses;
		foreach($this->mAdditionalAttributes as $Additional)
			foreach($Additional->getClasses() as $class)
				$classes[] = $class;
		return $classes;
	}

	/**
	 * Return the style value or a name-value associative array
	 * @param null $name
	 * @return String|Array
	 */
	function getStyle($name = null) {
		$styles = $this->mStyles;
		foreach($this->mAdditionalAttributes as $Additional)
			$styles += $Additional->getStyle();

		if($name === null)
			return $styles;
		return isset($styles[$name]) ? $styles[$name] : null;
	}

    /**
     * Checks to see if a class exists in the class list
     * @param String $class
     * @return bool
     */
    function hasClass($class) {
	    foreach($this->getClasses() as $class2) {
		    if($class === $class2)
			    return true;
	    }
	    return false;
    }

    function hasAttr($attrName) {
        return isset($this->mAttr[$attrName]);
    }

	/**
	 * Add a css class to the collection
	 * @param String $class one or multiple css classes
	 * @param null $_class [varargs]
	 * @return int
	 */
    function addClass($class, $_class=null) {
	    $classes = is_array($class)
		    ? $class
			: (strpos($class, ' ') !== false
			    ? preg_split('/\s+/', $class)
				: func_get_args());
	    $c = 0;
	    foreach($classes as $class) {
		    if(!$class || in_array($class, $this->mClasses))
			    continue;
		    $c++;
		    $this->mClasses[] = $class;
	    }
	    return $c;
    }

    /**
     * Add css styles to the collection
     * @param String $styleList one or multiple css styles
     */
    function addStyles($styleList) {
	    if(preg_match_all('/(\w+):\s+([\w\s,]+);?/', $styleList, $matches)) {
		    foreach($matches[1] as $name) {
			    $this->setStyle($name, $matches[2][$name]);
		    }
	    }
    }

    /**
     * Add a css style to the collection
     * @param $name
     * @param $value
     */
    function setStyle($name, $value) {
	    $this->mStyles[$name] = $value;
    }

	/**
	 * Render html attributes
	 * @param IAttributes|null $Additional
	 * @param IAttributes $_Additional
	 * @return string|void always returns void
	 */
	function render(IAttributes $Additional = null, IAttributes $_Additional = null) {
		$classes = $this->getClasses();
		$styles = $this->getStyle();
		$attributes = $this->getAttribute();

		if($Additional || $_Additional) {
			$AdditionalList = $this->mAdditionalAttributes;
			foreach(func_get_args() as $arg)
				if($arg)
					$AdditionalList[] = $arg;
			foreach($AdditionalList as $Additional) {
				$classes = array_merge($classes, $Additional->getClasses());
				$styles += $Additional->getStyle();
				$attributes += $Additional->getAttribute();
			}
		}

		if($classes) {
			$i=0;
			echo ' class=\'';
			foreach($classes as $class)
				if($class)
					echo ($i++ ? ' ' : ''), $class;
			echo '\'';
		}

		if($styles) {
			$i=0;
			echo ' style=\'';
			foreach($styles as $name=>$value)
				echo ($i++ ? '; ' : ''), $name, ": ", $value;
			echo '\'';
		}

		foreach($attributes as $key => $value)
			echo ' ', $key, '="', str_replace('"', "'", $value), '"';
	}

	function __toString() {
		ob_start();
		$content = ob_get_contents();
		ob_end_clean();
		return $content;
	}
}