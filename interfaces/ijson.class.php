<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Interfaces;

interface IJSON {
    /**
     * EXPORT Object to an associative array to be formatted into JSON
     * @param Array $JSON the JSON array to modify
     * @return void
     */
    function toJSON(Array &$JSON);
}