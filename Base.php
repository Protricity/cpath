<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath;

use CPath\Backend\CPathBackendRoutes;
use CPath\Framework\Request\Common\CLIRequest;
use CPath\Framework\Request\Common\WebRequest;
use CPath\Request\IRequest;
use CPath\Interfaces\IAutoLoader;
use CPath\Loaders\CPathLoader;

/**
 * Class Base
 * @package CPath
 *
 * Provides required framework functionality such as class loaders and directories
 */

class Base {

    /** @var IAutoLoader[] */
    private static $mLoaders = array();
    private static $mBasePath;

    static function init() {
        self::$mBasePath = dirname(__DIR__) . "/";
        spl_autoload_register(__NAMESPACE__.'\Base::autoload', true);
        include 'Config.php';
        if(Config::$ProfileEnable)
            Profile::load();
    }

    public static function addLoader($namespace, IAutoLoader $Loader=NULL) {
        self::$mLoaders[$namespace] = $Loader ?: new CPathLoader();
    }

    /** Autoloader for CPath + registered namespaces. Path matches namespace hierarchy of Class */
    static function autoload($name) {
        if(stripos($name, 'CPath') === 0) {
            $name = strtr($name, '\\', '//');
            $classPath = self::$mBasePath . $name . '.php';
            include($classPath);
            return;
        }
        foreach(self::$mLoaders as $ns => $Loader) {
            if(stripos($name, $ns) === 0) {
                $Loader->loadClass($name);
                return;
            }
        }
    }

    /** Attempt to route a web request to it's destination */
    public static function render() {
        $Request = Config::chooseRequest();
        $Routes = new CPathBackendRoutes;
        $Routes->render($Request);
    }

    /**
     * Returns the lower-case public path for a Class Namespace
     * @param $Class String|Object the class name or instance
     * @param $withDomain boolean true if the full domain path should be returned
     * @return string the public path
     */
//    public static function getClassPath($Class, $withDomain=false) {
//        if(is_object($Class))
//            $Class = get_class($Class);
//        return ($withDomain ? Config::getDomainPath() : '/')
//            . trim(dirname(str_replace('\\', '/', ($Class))), '/') . '/';
//    }

//    /**
//     * Get the IRequest instance for this render
//     * @return \CPath\Request\IRequest
//     */
//    public static function getRequest() {
//        static $Request = NULL;
//        if($Request)
//            return $Request;
//
//        $Request = Config::chooseRequest();
//        return $Request;
//    }
//
//    public static function isCLI() {
//        static $cli = NULL;
//        return $cli !== NULL
//            ? $cli
//            : $cli = self::getRequest() instanceof CLIRequest;
//    }

    static function getBasePath() {
        return self::$mBasePath;
    }

}

// Static class initializes on include
Base::init();

// Static class initializes on include
Base::init();