<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 11/15/14
 * Time: 5:07 PM
 */
namespace CPath\Render\HTML\Attribute;

class HTMLTabStopAttribute implements IAttributesAggregate
{

	private static $IndexCount = 0;

	private $mTabIndex;

	public function __construct($tabindex = null) {
		$this->mTabIndex = $tabindex ? : self::$IndexCount++;
	}

	/**
	 * @return IAttributes
	 */
	function getAttributes() {
		return new Attributes('tabindex', $this->mTabIndex);
	}

	// Static

	public static function getTabIndexCount() {
		return self::$IndexCount;
	}
}