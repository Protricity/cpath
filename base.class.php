<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath;

use CPath\Interfaces\IAutoLoader;
use CPath\Interfaces\IRequest;
use CPath\Interfaces\IRoute;
use CPath\Model\FileRequestRoute;
use CPath\Model\MissingRoute;
use CPath\Request\CLI;
use CPath\Request\Web;

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
    private static $isCLI = false;
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
        if(self::$mLoaded)
            return;
        if(self::getConfig('profile.enable', false)) {
            include 'profile.class.php';
            Profile::load();
        }
        spl_autoload_register(__NAMESPACE__.'\Base::loadClass', true);
        //spl_autoload_register(__NAMESPACE__.'\Base::errClass', true);
        //if(self::getConfig('build.auto')) Build::buildClasses(); // Depreciated. no reason to auto build
        self::$mLoaded = true;
    }

//    /**
//     * Register an autoloader.
//     * @param IAutoLoader $loader
//     */
//    public static function registerAutoloader(IAutoLoader $loader, $prefix) {
//        self::$mAutoloaders[strtolower($prefix)] = $loader;
//    }

    /** Deactivate Autoloader for classes */
    public static function unload() {
        if(self::$mLoaded) {
            spl_autoload_unregister(__NAMESPACE__.'\Base::errClass');
            spl_autoload_unregister(__NAMESPACE__.'\Base::loadClass');
            self::$mLoaded = false;

        }
    }

    /** Autoloader for classes. Path matches namespace heirarchy of Class */
    private static function loadClass($name) {
//        if(strpos($name, '\\')===false) return;
//      $prefix = strtolower(strstr($name, '\\', true));
//        if(isset(self::$mAutoloaders[$prefix])
//            && self::$mAutoloaders[$prefix]->loadClass($name) !== false)
//                return;
        $name = strtr(strtolower($name), '_\\', '//');
        $classPath = self::$mBasePath . $name . '.class.php';
        //if(file_exists($classPath))
            include($classPath);
        //else
        //    Log::e(__CLASS__, "Class not found: ".$classPath);
            //throw new ClassNotFoundException(__CLASS__."::loadClass: {$name} not found in {$classPath}");

    }

    /** Loaded after main autoloader to throw an exception for missing classes
     * @param String $name the Class name
     * @throws ClassNotFoundException when a class is not found */
    private static function errClass($name) {
        $name = strtr('_\\', '//', strtolower($name));
        $classPath = self::$mBasePath . $name . '.class.php';
        throw new ClassNotFoundException("Class '{$name}' could not be found: ".$classPath);
    }

    /** Attempt to route a web request to it's destination */
    public static function render() {
        self::load();
        $Request = self::getRequest();
        $Request->findRoute()
            ->render($Request);
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
     * Returns the public path for a Class
     * @param $Class String|Object the class name or instance
     * @param $withDomain boolean true if the full domain path should be returned
     * @return string the public path
     */
    public static function getClassPublicPath($Class, $withDomain=true) {
        if(is_object($Class))
            $Class = get_class($Class);
        return ($withDomain ? self::getDomainPath() : '')
            .dirname(str_replace('\\', '/', strtolower($Class))).'/';
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
     * Get the IRequest instance for this render
     * @return IRequest
     */
    public static function getRequest() {
        static $Request = NULL;
        if($Request) return $Request;

        if(!empty($_SERVER['argv'])) {
            $Request = CLI::fromRequest();
        } else {
            $Request = Web::fromRequest();
        }
        return $Request;
    }

    public static function isCLI() {
        static $cli = NULL;
        return $cli !== NULL
            ? $cli
            : $cli = self::getRequest() instanceof CLI;
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
     * Returns an array of config variable based on a prefix
     * @param $prefix string The name of the config variable
     * @return array an array of config variables
     */
    public static function getConfigByPrefix($prefix) {
        $arr = array();
        foreach(self::$mConfig as $key => $data)
            if(strpos($key, $prefix) === 0)
                $arr[substr($key, strlen($prefix))] = $data;
        return $arr;
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
