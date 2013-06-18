<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath;

class Base {
    private static $mLoaded, $mBasePath, $mLog = array(), $mDebug = false;

    public static function init() {
        self::$mBasePath = dirname(__DIR__) . "/";
    }

    public static function load() {
        if(!self::$mLoaded) {
            spl_autoload_register(__NAMESPACE__.'\Base::loadClass', true);
            if(self::$mDebug) Build::classes();
        }
    }

    public static function unload() {
        spl_autoload_unregister(__NAMESPACE__.'\Base::loadClass');
        self::$mLoaded = false;
    }

    private static function loadClass($name) {
        if(strpos($name, '\\')===false) return;
        //$name = str_replace('\\', '/', strtolower($name));
        $name = strtolower($name);
        $classPath = self::$mBasePath . $name . '.class.php';
        include_once($classPath);
    }

    public static function routeRequest() {
        self::load();
        Route::tryAllRoutes();
    }

    public static function getBasePath() {
        return static::$mBasePath;
    }

    public static function getGenPath() {
        static $gen = NULL;
        return $gen ?: $gen = self::getBasePath().'gen/';
    }

    public static function log($msg) {
        self::$mLog[] = $msg;
    }

    public static function getLog() {
        return self::$mLog;
    }

    public static function isDebug() {
        return self::$mDebug;
    }

    public static function setDebug($debug=true) {
        self::$mDebug = $debug;
    }
}

Base::init();