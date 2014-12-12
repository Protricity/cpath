<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/27/14
 * Time: 3:39 PM
 */
namespace CPath\Render\HTML\Attribute;

use CPath\Request\IRequest;

class ClassAttributes implements IAttributes
{
	private $mClasses = array();
	function __construct($className=null, $_className=null) {
		foreach(func_get_args() as $arg) {
			$this->addClass($arg);
		}
	}

	public function addClass($className, $_className=null) {
		foreach(func_get_args() as $arg) {
			foreach(preg_split('/\s+/', $arg) as $className) {
				if($className) {
					$this->mClasses[] = $className;
				}
			}
		}
	}

	/**
	 * Get an array of classes
	 * @return Array
	 */
	function getClasses() {
		return $this->mClasses;
	}

	/**
	 * Render html attributes
	 * @param IRequest $Request
	 * @internal param \CPath\Render\HTML\Attribute\IAttributes|null $Additional
	 * @return string|void always returns void
	 */
	function renderHTMLAttributes(IRequest $Request=null) {
		echo ' class="';
		$this->renderHTMLClassAttributeValue($Request);
		echo '"';
	}

	/**
	 * Return an associative array of attribute name-value pairs
	 * @param \CPath\Request\IRequest $Request
	 * @return string
	 */
	function getHTMLAttributeString(IRequest $Request=null) {
		return ' class="' . $this->getHTMLClassAttributeString($Request) . '"';
	}

	/**
	 * Render class attribute
	 * @param IRequest $Request
	 * @internal param \CPath\Render\HTML\Attribute\IAttributes|null $Additional
	 * @return string|void always returns void
	 */
	function renderHTMLClassAttributeValue(IRequest $Request=null) {
		foreach($this->getClasses() as $i => $class) {
			if($i > 0)
				echo ' ';
			echo $class;
		}
	}

	/**
	 * Return an associative array of attribute name-value pairs
	 * @param \CPath\Request\IRequest $Request
	 * @return string
	 */
	function getHTMLClassAttributeString(IRequest $Request = null) {
		return implode(' ', $this->getClasses());
	}

	/**
	 * Get html attribute string
	 * @return String
	 */
	function __toString() {
		return $this->getHTMLAttributeString();
	}
}

