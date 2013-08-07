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
     * Generate a set of short options from a set of fields
     * @param array $fields the fields to process
     * @return array the list of short options
     */
    function processShortOptions(Array $fields);
}