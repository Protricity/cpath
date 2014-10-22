<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/27/14
 * Time: 3:39 PM
 */
namespace CPath\Render\HTML\Attribute;

final class ClassAttributes implements IAttributes
{
    private $mClasses = array();

    public function __construct($classList, $_classList = null) {
        foreach (func_get_args() as $arg)
	        if($arg)
                $this->addClass($arg);
    }

    /**
     * Add a css class to the collection
     * @param Array|String $classList one or multiple css classes
     */
    function addClass($classList) {
        if(!is_array($classList))
            $classList = preg_split('/\s+/', $classList);
        foreach ($classList as $class)
            $this->mClasses[$class] = true;
    }

    /**
     * Checks to see if a class exists in the class list
     * @param $class
     * @return bool
     */
    function hasClass($class) {
        return !empty($this->mClasses[$class]);
    }

    /**
     * Get html attribute string
     * @return String
     */
    function __toString() {
        return ' class=\'' . implode(' ', array_keys($this->mClasses)) . '\'';
    }

    /**
     * Merge attributes and return an instance
     * @param IAttributes|null $Attributes
     * @return IAttributes
     */
    function merge(IAttributes $Attributes=null) {
        if(!$Attributes)
            return $this;
        $Attr = new HTMLAttributes($Attributes);
        foreach($this->mClasses as $class => $true)
            $Attr->addClass($class);
        return $Attr;
    }
}