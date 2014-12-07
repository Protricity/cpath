<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 11/13/14
 * Time: 9:49 PM
 */
namespace CPath\Render\HTML\Attribute;

use CPath\Request\IRequest;

class StyleAttributes implements IStyleAttributes, IAttributes
{
	private $mStyle = array();
	function __construct($styleName=null, $styleValue=null, $_styleName=null, $_styleValue=null) {
		for($i = 0; $i < func_num_args(); $i+=2) {
			$styleName = func_get_arg($i);
			$styleValue = func_get_arg($i+1);
			if($styleName)
				$this->addStyle($styleName, $styleValue);
		}
	}

	public function addStyle($styleName, $styleValue) {
		$this->mStyle[$styleName] = $styleValue;
		return $this;
	}

	public function getStyleValue($styleName) {
		return isset($this->mStyle[$styleName]) ? $this->mStyle[$styleName] : null;
	}

	/**
	 * Get an associative array of stylesheet values
	 * @return Array
	 */
	function getStyleSheetList() {
		return $this->mStyle;
	}

	/**
	 * Render html attributes
	 * @param IRequest $Request
	 * @internal param \CPath\Render\HTML\Attribute\IAttributes|null $Additional
	 * @return string|void always returns void
	 */
	function renderHTMLAttributes(IRequest $Request=null) {
		echo ' style="';
		$this->renderHTMLStyleAttributeValue($Request);
		echo '"';
	}


	/**
	 * Return an associative array of attribute name-value pairs
	 * @param \CPath\Request\IRequest $Request
	 * @return string
	 */
	function getHTMLAttributeString(IRequest $Request=null) {
		return ' style="' . $this->getHTMLStyleAttributeString($Request) . '"';
	}


	/**
	 * Render style attribute
	 * @param IRequest $Request
	 * @return string|void always returns void
	 */
	function renderHTMLStyleAttributeValue(IRequest $Request=null) {
		$i=0;
		foreach($this->getStyleSheetList() as $name=>$value)
			echo ($i++ ? ' ' : '') . $name, ': ', str_replace('"', "'", $value), ';';
	}

	/**
	 * Return an associative array of attribute name-value pairs
	 * @param \CPath\Request\IRequest $Request
	 * @return string
	 */
	function getHTMLStyleAttributeString(IRequest $Request = null) {
		$content = '';
		foreach($this->getStyleSheetList() as $name=>$value)
			$content .= ($content ? ' ' : '') . $name . ': ' . str_replace('"', "'", $value) . ';';
		return $content;
	}

	/**
	 * Get html attribute string
	 * @return String
	 */
	function __toString() {
		return $this->getHTMLAttributeString();
	}
}

