<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 11/27/14
 * Time: 12:34 AM
 */
namespace CPath\Render\Helpers;

/**
 * A simple helper class used to render consistent indentations in html
 * Class RenderIndents
 * @package CPath\Misc
 */
final class RenderIndents
{

	private $mTabString, $mCount;

	function __construct($tabString = "\t", $count = 0) {
		$this->setIndent($count, $tabString);
	}

	/**
	 * Render an indentation
	 * @param int $addCount the number of tabs to add for this render
	 * @param String|Null $newLine optionally append a newline character
	 * @return String
	 */
	public function indent($addCount = 0, $newLine = '') {
		echo $newLine, str_repeat($this->mTabString, $this->mCount + $addCount);
	}

	/**
	 * Render an indentation
	 * @param int $addCount the number of tabs to add for this render
	 * @param String|Null $newLine optionally append a newline character
	 * @return String
	 */
	public function getIndent($addCount = 0, $newLine = '') {
		return $newLine . str_repeat($this->mTabString, $this->mCount + $addCount);
	}

	/**
	 * Update the current tab count and optionally replace the characters used
	 * @param int|null $tabCount the number of total tabs to set or null to keep count as is
	 * @param null|String $newTab
	 * @return RenderIndentsEnd to reset the tabs
	 */
	public function setIndent($tabCount = null, $newTab = null) {
		$R = new RenderIndentsEnd($this->mCount, $this->mTabString);
		if ($newTab)
			$this->mTabString = $newTab;
		if ($tabCount !== null)
			$this->mCount = $tabCount;

		return $R;
	}

	/**
	 * Update the current tab count and optionally replace the characters used
	 * @param int $addCount the number tabs to add to the total
	 * @return $this
	 */
	public function addIndent($addCount) {
		$this->mCount += $addCount;
		if ($this->mCount < 0)
			$this->mCount = 0;

		return $this;
	}

	// Statics

	private static $mStatic = null;

	public static function get() {
		return self::$mStatic ? : self::$mStatic = new RenderIndents();
	}

	/** Shorthand for ::get()->indent($addCount);
	 * @param int $addCount the number of tabs to add for this render
	 * @return String always returns null
	 */
	public static function i($addCount = 0) {
		static::get()->indent($addCount);
	}

	/** Shorthand for ::get()->indent($addCount) prepended by a new line character
	 * @param int $addCount the number of tabs to add for this render
	 * @return String always returns null
	 */
	public static function ni($addCount = 0) {
		echo "\n";
		static::get()->indent($addCount);
	}

	/** Shorthand for ::get()->setIndent($addCount)
	 * @param int|null $tabCount the number of total tabs to set or null to keep count as is
	 * @param null $newTab
	 * @return RenderIndentsEnd to reset the tabs
	 */
	public static function si($tabCount = null, $newTab = null) {
		return static::get()
			->setIndent($tabCount, $newTab);
	}

	/** Shorthand for ::get()->addIndent($addCount)
	 * @param int $addCount the number of total tabs to set
	 * @return String always returns null
	 */
	public static function ai($addCount = 0) {
		static::get()
			->addIndent($addCount);
	}

}

