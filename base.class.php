<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath;

use CPath\Builders\Installer;
use CPath\Interfaces\IAutoLoader;
use CPath\Interfaces\IRequest;
use CPath\Loaders\SimpleLoader;
use CPath\Misc\ConfigBuilder;
use CPath\Request\CLI;
use CPath\Request\Web;

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
        spl_autoload_register('self::autoload', true);
        include 'config.class.php';
    }

    public static function addLoader($namespace, IAutoLoader $Loader=NULL) {
        self::$mLoaders[$namespace] = $Loader ?: new SimpleLoader();
    }

    /** Autoloader for CPath + registered namespaces. Path matches namespace hierarchy of Class */
    private static function autoload($name) {
        if(stripos($name, 'CPath') === 0) {
            $name = strtr(strtolower($name), '_\\', '//');
            $classPath = self::$mBasePath . $name . '.class.php';
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
        $Request = self::getRequest();
        $Request->findRoute()
            ->render($Request);
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
        return ($withDomain ? Config::$Domain : '')
            .dirname(str_replace('\\', '/', strtolower($Class))).'/';
    }

    /**
     * Get the IRequest instance for this render
     * @return IRequest
     */ // TODO: move to abstract class
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

}

// Static class initializes on include
Base::init();
