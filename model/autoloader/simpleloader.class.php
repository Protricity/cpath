<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Model\AutoLoader;

class SimpleLoader extends BaseLoader {
    private $mPrefix, $mSearch, $mReplace;
    function __construct($prefix, $search='\\', $replace='/') {
        $this->mPrefix = $prefix;
        $this->mSearch = $search;
        $this->mReplace = $replace;
    }

    /**
     * Attempts to register a class file from the class name.
     * @param $class String the full class name
     * @return boolean false if the class was not found
     */
    function autoLoad($class) {
        $class = strtr($class, $this->mSearch, $this->mReplace);
        $classPath = $this->mPrefix . $class . '.php';
        include $classPath;
    }

    // Static

    static function register($namespace, $prefix, $search='\\', $replace='/') {
        parent::registerLoader($namespace, new self($prefix, $search, $replace));
    }
}