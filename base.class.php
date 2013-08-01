<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath;

use CPath\Interfaces\IAutoLoader;
use CPath\Interfaces\IRoute;
use CPath\Model\FileRequestRoute;
use CPath\Model\MissingRoute;

class ClassNotFoundException extends \Exception {}
/**
 * Class Base
 * @package CPath
 *
 * Provides required framework functionality such as class autoloader and directories
 */
class Base {
    private static $mLoaded = false;
    private static $mBasePath;
    private static $mConfig;
    /** @var IAutoLoader[] $mAutoloaders */
    private static $mAutoloaders = array();

    /** Initialize Static Class on include */
    public static function init() {
        self::$mBasePath = dirname(__DIR__) . "/";
        $config = self::loadConfig();
        if(!$config) {
            $loaded = self::$mLoaded;
            if(!$loaded) self::load();
            include 'build.class.php';
            $config = Build::buildConfig();
            if(!$loaded) self::unload();
        }
        self::$mConfig = $config;
    }

    public static function loadConfig() {
        $config = array();
        $path = self::getGenPath().'config.php';
        if(file_exists($path))
            include $path;
        return $config;
    }

    /** Activate Autoloader for classes */
    public static function load() {
        if(!self::$mLoaded) {
            spl_autoload_register(__NAMESPACE__.'\Base::loadClass', true);
            //if(self::getConfig('build.auto')) Build::buildClasses(); // Depreciated. no reason to auto build
        }
    }

    /**
     * Register an autoloader.
     * @param IAutoLoader $loader
     */
    public static function registerAutoloader(IAutoLoader $loader, $prefix) {
        self::$mAutoloaders[strtolower($prefix)] = $loader;
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
        $prefix = strtolower(strstr($name, '\\', true));
        if(isset(self::$mAutoloaders[$prefix])
            && self::$mAutoloaders[$prefix]->loadClass($name) !== false)
                return;
        $name = str_replace('\\', '/', strtolower($name));
        $name = strtolower($name);
        $classPath = self::$mBasePath . $name . '.class.php';
        if(file_exists($classPath))
            include($classPath);
        else
            Log::e(__CLASS__, "Class not found: ".$classPath);
            //throw new ClassNotFoundException(__CLASS__."::loadClass: {$name} not found in {$classPath}");

    }

    /**
     * Attempt to find a Route
     * @param null $routePath the path to match
     * @return IRoute the route instance found. MissingRoute is returned if no route was found
     */
    public static function findRoute($routePath=NULL) {
        if(!$routePath) $routePath = Util::getUrl('route');
        if(preg_match('/\.\w+$/', $routePath)) {
            $Route = new FileRequestRoute($routePath);
        } elseif(Base::isApcEnabled()) {
            $Route = RouterAPC::findRoute($routePath);
        } else {
            $Route = Router::findRoute($routePath);
        }
        return $Route ?: new MissingRoute($routePath);
    }

    /** Attempt to route a web request to it's destination */
    public static function render() {
        self::load();
        self::findRoute()
            ->render();
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


    /** Returns the domain path */
    public static function getClassPublicPath($Class) {
        return self::getDomainPath()
            .dirname(str_replace('\\', '/', strtolower(get_class($Class)))).'/';
    }

    /**
     * Returns true if debug mode is set
     * @return bool true if debug mode is set
     */
    public static function isDebug() {
        return self::$mConfig['debug'];
    }

    /**
     * Returns true if debug mode is set
     * @return bool true if debug mode is set
     */
    public static function isApcEnabled() {
        return self::$mConfig['apc.enabled'];
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

    public static function commitConfig($key, $value=NULL) {
        if(!is_array($key)) $key = array($key=>$value);
        foreach($key as $k => $v)
            self::setConfig($k, $v);
        Build::buildConfig($key);
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
