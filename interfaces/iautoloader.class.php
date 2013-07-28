<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Ari
 * Date: 7/26/13
 * Time: 1:25 PM
 * To change this template use File | Settings | File Templates.
 */
namespace CPath\Interfaces;

interface IAutoLoader {
    /**
     * Attempts to register a class file from the class name.
     * @param $class String the full class name
     * @return boolean false if the class was not found
     */
    function loadClass($class);
}