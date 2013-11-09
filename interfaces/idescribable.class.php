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
     * Get the Object Title
     * @return String description for this Object
     */
    function getTitle();

    /**
     * Get the Object Description
     * @return String description for this Object
     */
    function getDescription();
}
