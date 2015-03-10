<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Render\HTML\Attribute;

use CPath\Render\HTML\Attribute;
use CPath\Request\IRequest;

class Attributes implements IAttributes {
    private $mAttributes = array();

    function __construct($attrName=null, $attrValue=null, $_attrName=null, $_attrValue=null) {
	    $args = $attrName !== null ? func_get_args() : array();
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
					    $this->addAttributeHTML($v);
				    else
					    $this->setAttribute($k, $v);
			    }
		    }
	    }
    }


    /**
	 * Add an attribute to the collection
	 * @param String $attrName the attribute name. If null is provided, the attribute is not added
	 * @param String|null $value the attribute value. If null is provided,
	 * @return $this
	 * @throws \InvalidArgumentException if $replace == false and the attribute exists
	 * or $replace == true and the attribute does not exist
	 */
    function setAttribute($attrName, $value = null) {
        $this->mAttributes[$attrName] = $value;
	    return $this;
    }

	function hasAttribute($attrName) {
		return isset($this->mAttributes[$attrName]);
	}

	function removeAttribute($attrName) {
		if(!$this->hasAttribute($attrName))
			return false;
		unset($this->mAttributes[$attrName]);
		return true;
	}

	function addAttributes(IAttributes $Attributes, IAttributes $_Attributes=null) {
		foreach(func_get_args() as $Attributes) {
			$this->mAttributes[] = $Attributes;
		}
	}

	/**
	 * Return the attribute value
	 * @param String $name
	 * @return String|null
	 */
	function getAttribute($name) {
		foreach($this->mAttributes as $key => $Attr) {
			if($Attr instanceof IAttributes) {
				if(null !== ($value = $Attr->getAttribute($name))) {
					return $value;
				}
			} elseif ($name === $key) {
				return $this->mAttributes[$name];
			}
		}
		return null;
	}

	function getAttributes() {
		return $this->mAttributes;
	}

	/**
	 * Add attributes
	 * @param $htmlAttr
	 * @throws \InvalidArgumentException
	 */
	function addAttributeHTML($htmlAttr) {
		if(preg_match_all('/([a-z0-9_]+)\s*=\s*[\"\'](.*?)[\"\']/is', $htmlAttr, $matches)) {
			foreach($matches[1] as $i => $name) {
				$this->setAttribute($name, $matches[2][$i]) ;
			}

		} else {
			if(strpos($htmlAttr, '=') !== false)
				throw new \InvalidArgumentException("Invalid element html: " . $htmlAttr);
			$this->addClass($htmlAttr);
		}
	}
	/**
	 * Returns an array of classes
	 * @return Array
	 */
	public function getClasses() {
		if(!empty($this->mAttributes['class']))
			return preg_split('/\s+/', $this->mAttributes['class']);
		return array();
	}

	/**
	 * Checks to see if a class exists in the class list
	 * @param String $className
	 * @return bool
	 */
	public function hasClass($className) {
		if(!empty($this->mAttributes['class']))
			return preg_match('/(?:^| )' . preg_quote($className) . '(?: |$)/i', $this->mAttributes['class']);
		return false;
	}


	public function addClass($classList) {
		if(!is_array($classList))
			$classList = func_get_args();
		foreach($classList as $class) {
			if(empty($this->mAttributes['class']))
				$this->mAttributes['class'] = $class;
			else
				$this->mAttributes['class'] .= ' ' . $class;
		}
		return $this;
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
	 * @return $this
	 */
    function setStyle($name, $value) {
	    $styles = $this->getStyle();
	    $styles[$name] = $value;
	    $styleList = array();
	    foreach($styles as $name => $value)
			$styleList[] = $name . ': ' . $value;

		$this->mAttributes['style'] = implode(';', $styleList) . ';';

	    return $this;
    }

	/**
	 * Return the style value or a name-value associative array
	 * @param String|null $name
	 * @return String|null|Array
	 */
	function getStyle($name = null) {
		if(empty($this->mAttributes['style']))
			return $name === null ? null : array();

		$stylesList = explode(';', $this->mAttributes['style']);
		$styles = array();
		foreach($stylesList as &$style) {
			list($name, $value) = explode(':', $style, 2);
			$styles[trim($name)] = trim($value);
		}
		if($name === null)
			return $styles;

		if(!isset($styles[$name]))
			return null;

		return $this->mAttributes['style'][$name];
	}

	/**
	 * Render html attributes
	 * @param IRequest|null $Request
	 * @internal param bool $return
	 * @return string|void always returns void
	 */
	function renderHTMLAttributes(IRequest $Request=null) {
		foreach($this->mAttributes as $value) {
			if($value instanceof IAttributes) {
				$value->renderHTMLAttributes($Request);
			}
		}

		foreach($this->mAttributes as $name => $value)
			if(is_string($name)) {
				if(strpos($value, '"') !== false) {
					if(strpos($value, "'") !== false) {
						echo ' ', $name, "='", str_replace('"', '`', str_replace("'", '`', $value)), "'";
					} else {
						echo ' ', $name, "='", str_replace("'", '"', $value), "'";
					}
				} else if(strpos($value, "'") !== false) {
					echo ' ', $name, '="', str_replace('"', "'", $value), '"';
				} else {
					echo ' ', $name, '="', $value, '"';
				}
			}
	}

	/**
	 * Return an associative array of attribute name-value pairs
	 * @param \CPath\Request\IRequest $Request
	 * @return string
	 */
	function getHTMLAttributeString(IRequest $Request=null) {
		$oldAttr = $this->mAttributes;
		foreach($this->mAttributes as $value) {
			if($value instanceof IAttributes) {
				$html = $value->getHTMLAttributeString($Request);
				$this->addAttributeHTML($html);
			}
		}

		$content = '';
		foreach($this->mAttributes as $name => $value)
			if(is_string($name)) {
				if (strpos($value, '"') !== false) {
					if (strpos($value, "'") !== false) {
						$content .= ' ' . $name . "='" . str_replace('"', '`', str_replace("'", '`', $value)) . "'";
					} else {
						$content .= ' ' . $name . "='" . str_replace("'", '"', $value) . "'";
					}
				} else if (strpos($value, "'") !== false) {
					$content .= ' ' . $name . '="' . str_replace('"', "'", $value) . '"';
				} else {
					$content .= ' ' . $name . '="' . $value . '"';
				}
			}

		$this->mAttributes = $oldAttr;
		return $content;
	}

	function __toString() {
		return $this->getHTMLAttributeString();
	}
}