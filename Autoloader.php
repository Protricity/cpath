<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/7/14
 * Time: 2:59 PM
 */
namespace CPath;

use CPath\Interfaces\IAutoLoader;
use CPath\Loaders\CPathLoader;

class Autoloader
{
    /** @var String[] */
    private static $mLoaders = array();

    /**
     * @param String $namespace with trailing backslash
     * @param String|Callable $path
     */
    public static function addLoader($namespace, $path) {
        if(is_string($path))
            $path = trim($path, '\\') . '\\';
        self::$mLoaders[$namespace] = $path;
    }

    /** Autoloader for CPath + registered namespaces. Path matches namespace hierarchy of Class */
    static function loadClass($name) {
        foreach (self::$mLoaders as $prefix => $path) {
            if (stripos($name, $prefix) === 0) {
                if(is_callable($path))
                    $path = $path($name);
                else
                    $path = $path . substr($name, sizeof($prefix)) . '.php';
                include($path);
                return;
            }
        }
    }

}
Autoloader::addLoader(__NAMESPACE__, __DIR__);
spl_autoload_register(__NAMESPACE__ . '\Autoloader::loadClass', true);