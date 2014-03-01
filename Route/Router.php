<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Route;
use CPath\Config;
use CPath\Framework\Build\API\Build;

/**
 * Class Router - finds routes to match paths and renders them
 * @package CPath
 */
final class Router{

    const APC_PREFIX = 'cpath.route.%s:';

    // Static methods

    /**
     * Loads all routes and attempts to match them to the request path
     * @param String $routePath the path to find
     * @param Array $args the arguments parsed out of the route if found
     * @return IRoute|NULL the found route or null if none found
     */
    public static function findRoute($routePath, &$args) {

        $routes = self::getRoutes();
        /** @var IRoute $Route */
        foreach($routes as $Route) {
            if(!$Route->match($routePath, $args))
                continue;
            return $Route;
        }
        return NULL;
    }

    public static function getRoutes($method=NULL) {
        $routes = array();
        $path = Config::getGenPath().'routes.gen.php';
        if(!file_exists($path) || !(include $path) || !$routes) {
            Build::build();
            require $path;
        }
        if(!$method)
            return $routes;
        $routes2 = array();
        foreach($routes as $route)
            if(stripos($route[0], $method) === 0)
                $routes2[] = $route;
        return $routes2;
    }

//    private static function processDefaults(IRoute $Route, Array $defaults) {
//        foreach($defaults as $def) {
//            if(is_array($def))
//                $Route->setRequest($def);
//            else
//                $Route->addArg($def);
//        }
//    }
}