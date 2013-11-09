<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Interfaces;

interface IShortOptions {

    /**
     * Add or generate a short option for the list
     * @param String $fieldName the field name
     * @param String $shortName the short name representing the field name
     */
    function processShortOption($fieldName, $shortName);
}