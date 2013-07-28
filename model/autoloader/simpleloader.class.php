<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Model\AutoLoader;

use CPath\Interfaces\IAutoLoader;

class SimpleLoader implements IAutoLoader {
    private $mPrefix;
    public function __construct($prefix) {
        $this->mPrefix = $prefix;
    }

    /**
     * Attempts to register a class file from the class name.
     * @param $class String the full class name
     * @return boolean false if the class was not found
     */
    function loadClass($class)
    {
        $class = str_replace('\\', '/', $class);
        $classPath = $this->mPrefix . $class . '.php';
        if(file_exists($classPath)) {
            include $classPath;
            return true;
        }
        return false;
    }
}