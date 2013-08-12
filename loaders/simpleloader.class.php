<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Loaders;

use CPath\Base;
use CPath\Config;
use CPath\Interfaces\ClassNotFoundException;
use CPath\Interfaces\IAutoLoader;

class SimpleLoader implements IAutoLoader {
    private $mPrefix;
    function __construct($prefix=NULL) {
        $this->mPrefix = $prefix ?: Config::$BasePath;
    }

    /**
     * Attempts to register a class file from the class name.
     * @param $class String the full class name
     */
    function loadClass($class) {
        $class = strtr(strtolower($class), '_\\', '//');
        $classPath = $this->mPrefix . $class . '.class.php';
        if(!(include $classPath))
            throw new ClassNotFoundException("Class '{$class}' could not be found: ".$classPath);
    }
}