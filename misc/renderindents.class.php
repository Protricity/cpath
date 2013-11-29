<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Ari
 * Date: 7/27/13
 * Time: 3:54 PM
 * To change this template use File | Settings | File Templates.
 */
namespace CPath\Misc;

/**
 * A simple helper class used to render consistent indentations in html
 * Class RenderIndents
 * @package CPath\Misc
 */
final class RenderIndents {

    private $mTabString, $mCount, $mTab;

    function __construct($tabString="\t", $count=0) {
        $this->setIndent($count, $tabString);
    }

    /**
     * Render an indentation
     * @param int $addCount the number of tabs to add for this render
     * @param String|Null $newLine optionally append a newline character
     * @return String
     */
    public function indent($addCount=0, $newLine='') {
        echo $newLine . str_repeat($this->mTabString, $this->mCount + $addCount);
    }

    /**
     * Update the current tab count and optionally replace the characters used
     * @param int $tabCount the number of total tabs to set
     * @param null $newTab
     * @return $this
     */
    public function setIndent($tabCount, $newTab=NULL) {
        if($newTab)
            $this->mTabString = $newTab;
        $this->mCount = $tabCount;
        return $this;
    }

    /**
     * Update the current tab count and optionally replace the characters used
     * @param int $addCount the number tabs to add to the total
     * @return $this
     */
    public function addIndent($addCount) {
        $this->mCount += $addCount;
        if($this->mCount < 0)
            $this->mCount = 0;
        return $this;
    }

    // Statics

    private static $mStatic = null;

    public static function get() {
        return self::$mStatic ?: self::$mStatic = new RenderIndents();
    }

    /** Shorthand for ::get()->indent($addCount);
     * @param int $addCount the number of tabs to add for this render
     * @return String always returns null
     */
    public static function i($addCount=0) {
        static::get()->indent($addCount);
    }

    /** Shorthand for ::get()->indent($addCount) prepended by a new line character
     * @param int $addCount the number of tabs to add for this render
     * @return String always returns null
     */
    public static function ni($addCount=0) {
        echo "\n";
        static::get()->indent($addCount);
    }

    /** Shorthand for ::get()->setIndent($addCount)
     * @param int $tabCount the number of total tabs to set
     * @param null $newTab
     * @return String always returns null
     */
    public static function si($tabCount=0, $newTab=null) {
        static::get()
            ->setIndent($tabCount, $newTab);
    }

    /** Shorthand for ::get()->addIndent($addCount)
     * @param int $addCount the number of total tabs to set
     * @return String always returns null
     */
    public static function ai($addCount=0) {
        static::get()
            ->addIndent($addCount);
    }

}