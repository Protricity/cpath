<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Model\AutoLoader;


abstract class BaseLoader {

    /**
     * Attempts to register a class file from the class name.
     * @param $class String the full class name
     * @return boolean false if the class was not found
     */
    abstract function autoLoad($class);

    // Static

    /** @var BaseLoader[] $mLoaders */
    private static $mLoaders = array();
    private static $mLoaded = false;

    protected static function registerLoader($namespace, BaseLoader $Loader) {
        self::$mLoaders[$namespace] = $Loader;
        self::load();
    }

    protected static function loadAll($className) {
        foreach(self::$mLoaders as $ns => $loader)
            if(stripos($className, $ns) === 0) {
                $loader->autoLoad($className);
                break;
            }
    }

    static function load() {
        if(!self::$mLoaded)
            spl_autoload_register('self::loadAll', true, true);
    }

    static function unload() {
        if(self::$mLoaded)
            spl_autoload_unregister ('self::loadAll', true, true);
    }
}