<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath;

/**
 * Class Base
 * @package CPath
 *
 * Provides required framework functionality such as class autoloader and directories
 */
class Base {
    private static $mLoaded = false, $mBasePath, $mConfig;

    /** Initialize Static Class on include */
    public static function init() {
        self::$mBasePath = dirname(__DIR__) . "/";
        $config = array();
        $path = self::getGenPath().'config.php';
        if(!file_exists($path) || !(include $path) || !$config) {
            $loaded = self::$mLoaded;
            if(!$loaded) self::load();
            include 'build.class.php';
            $config = Build::buildConfig();
            if(!$loaded) self::unload();
        }
        self::$mConfig = $config;
    }

    /** Activate Autoloader for classes */
    public static function load() {
        if(!self::$mLoaded) {
            spl_autoload_register(__NAMESPACE__.'\Base::loadClass', true);
            if(self::getConfig('build-auto')) Build::buildClasses();
        }
    }

    /** Deactivate Autoloader for classes */
    public static function unload() {
        if(self::$mLoaded) {
            spl_autoload_unregister(__NAMESPACE__.'\Base::loadClass');
            self::$mLoaded = false;
        }
    }

    /** Autoloader for classes. Path matches namespace heirarchy of Class */
    private static function loadClass($name) {
        if(strpos($name, '\\')===false) return;
        $name = str_replace('\\', '/', strtolower($name));
        $name = strtolower($name);
        $classPath = self::$mBasePath . $name . '.class.php';
        require_once($classPath);
    }

    /** Attempt to route a web request to it's destination */
    public static function routeRequest() {
        self::load();
        Route::tryAllRoutes();
    }

    /** Returns the path to the project directory */
    public static function getBasePath() {
        return static::$mBasePath;
    }

    /** Returns the path to the generated files directory */
    public static function getGenPath() {
        static $gen = NULL;
        return $gen ?: $gen = self::getBasePath().'gen/';
    }

    /** Returns the domain path */
    public static function getDomainPath() {
        return self::$mConfig['domain'];
    }

    /**
     * Returns true if debug mode is set
     * @return bool true if debug mode is set
     */
    public static function isDebug() {
        return self::$mConfig['debug'];
    }

    /**
     * Returns a config variable
     * @param $key string The name of the config variable
     * @param $default mixed the default value if the variable is not found
     * @return mixed mixed the value of the config variable
     */
    public static function getConfig($key, $default=NULL) {
        return isset(self::$mConfig[$key]) ? self::$mConfig[$key] : $default;
    }

    /**
     * Sets config variable for the active session.
     * @param $key string The name of the config variable
     * @param $value mixed The value for the config variable
     */
    public static function setConfig($key, $value) {
        self::$mConfig[$key] = $value;
    }

    /**
     * Set debug mode on and off
     * @param bool $debug set to true to enable debug mode
     */
    public static function setDebug($debug=true) {
        self::$mConfig['debug'] = $debug;
    }
}

// Static class initializes on include
Base::init();
