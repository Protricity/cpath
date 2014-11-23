<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Ari
 * Date: 7/27/13
 * Time: 3:54 PM
 * To change this template use File | Settings | File Templates.
 */
namespace CPath\Describable;

use CPath\Helpers\Strings;

class SimpleDescription implements IDescribable {
    private $mTitle = null;
    private $mDesc = null;

    function __construct($object) {
        if(!is_object($object)) {
            $this->mDesc = htmlentities((String) $object);
        } else {
            if(method_exists($object, '__toString')) {
                $this->mDesc = htmlentities((String) $object);
            } else {
                $this->mDesc = get_class($object);
            }
        }

        $this->mTitle = Strings::truncate($this->mDesc, 128, '...', false);
    }

    /**
     * Get the Object Title
     * @return String description for this Object
     */
    function getTitle() { return $this->mTitle; }

    /**
     * Set the Object Title
     * @param String $title the new description
     * @return $this
     */
    function setTitle($title) { $this->mTitle = $title; return $this; }


    /**
     * Get the Object Description
     * @return String description for this Object
     */
    function getDescription() { return $this->mDesc; }

    /**
     * Set the Object Description
     * @param String $desc the new description
     * @return $this
     */
    function setDescription($desc) { $this->mDesc = $desc; return $this; }

    /**
     * Get a simple world-visible description of this object as it would be used in a sentence (i.e. "User Acct 'root'")
     * Note: This method typically contains "return $this->getTitle();"
     * @return String simple description for this Object
     */
    function __toString() {
        return $this->getTitle();
    }
}