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
        $this->mPrefix = $prefix ?: Base::getBasePath();
    }

    /**
     * Attempts to register a class file from the class name.
     * @param $class String the full class name
     * @throws ClassNotFoundException if the class file was not found
     */
    function loadClass($class) {
        $class = strtr($class, '_\\', '//');
        $classPath = $this->mPrefix . $class . '.php';
        if(!(include $classPath))
            throw new ClassNotFoundException("Class '{$class}' could not be found: ".$classPath);
    }

    /**
     * Convenience loader for SimpleLoader
     * @param $namespace
     * @param $prefix
     */
    static function add($namespace, $prefix) {
        Base::addLoader($namespace, new self($prefix));
    }
}