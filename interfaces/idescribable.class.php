<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Interfaces;

interface IDescribable {

    /**
     * Get a simple public-visible title of this object as it would be displayed in a header (i.e. "Mr. Root")
     * @return String title for this Object
     */
    function getTitle();

    /**
     * Get a simple public-visible description of this object as it would appear in a paragraph (i.e. "User account 'root' with ID 1234")
     * @return String simple description for this Object
     */
    function getDescription();

    /**
     * Get a simple world-visible description of this object as it would be used when cast to a String (i.e. "root", 1234)
     * Note: This method typically contains "return $this->getTitle();"
     * @return String simple description for this Object
     */
    function __toString();
}
