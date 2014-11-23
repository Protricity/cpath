<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 11/15/14
 * Time: 5:07 PM
 */
namespace CPath\Render\HTML\Attribute;

class HTMLTabStopAttribute implements IAttributes
{

	private static $IndexCount = 0;

	private $mTabIndex;

	public function __construct($tabindex = null) {
		$this->mTabIndex = $tabindex ? : self::$IndexCount++;
	}

	/**
	 * Returns an array of classes
	 * @return Array
	 */
	function getClasses() {
		return array();
	}

	/**
	 * Return the style value or a name-value associative array
	 * @param null $name
	 * @return String|Array
	 */
	function getStyle($name = null) {
		return array();
	}

	/**
	 * Return the attribute value or a name-value associative array
	 * @param null $name
	 * @return String|Array
	 */
	function getAttribute($name = null) {
		$attributes = array(
			'tabindex' => $this->mTabIndex,
		);
		if ($name)
			return $attributes[$name];

		return $attributes;
	}

	/**
	 * Render html attributes
	 * @param IAttributes|null $Additional
	 * @return string|void always returns void
	 */
	function render(IAttributes $Additional = null) {
		foreach ($this->getAttribute() as $key => $value)
			echo ' ' . $key . "='" . $value . "'";
	}

	// Static

	public static function getTabIndexCount() {
		return self::$IndexCount;
	}
}