<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Interfaces;

interface IPHPExport {
    /**
     * EXPORT Object to PHP Code
     * @return String
     */
    function exportToPHP();

    /**
     * Instantiate an Object
     * @param Array $array associative array of data
     * @return IPHPExport|Object
     */
    static function __set_state($array);
}