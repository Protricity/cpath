<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Route;
use CPath\Compile;
use CPath\Framework\Route\Builders\RouteBuilder;
use CPath\Log;

/**
 * Class RouterAPC - finds routes in APC to match paths and renders them
 * @package CPath
 */
final class RouterAPC{

    const PREFIX = 'cpath.route';
    const PREFIX_ROUTE = 'cpath.route:';

    // Static methods

    /**
     * Loads all routes and attempts to match them to the request path
     * @param String $routePath the path to find
     * @param Array $args the arguments parsed out of the route if found
     * @return IRoute|NULL the found route or null if none found
     */
    public static function findRoute($routePath, &$args) {
        if(($inc = Compile::$BuildInc) && (Compile::$BuildInc != apc_fetch(self::PREFIX . '.inc'))) {
            RouteBuilder::rebuildAPCCache();
            apc_store(self::PREFIX . '.inc', $inc);
        }
        $route = $routePath;
        if(($max = Compile::$RouteMax ?: 0) && ($max < strlen($route))) {
            if($p = strrpos($route, '/', $max))
                $route = substr($route, 0, $p);
        }
        while(true) {
            $apcRoute = self::PREFIX_ROUTE.$route;
            $Route = apc_fetch($apcRoute, $found);
            if(!$found) {
                $p = strrpos($route, '/');
                if(!$p) break;
                $route = substr($route, 0, $p);
                continue;
            }
            $args = $Route->match($routePath);
            if($args === false) {
                Log::e(__CLASS__, "APC Cache did not match route: ". $route);
                apc_delete($apcRoute);
                continue;
            }
            return $Route;
        }
        if($Route = Router::findRoute($routePath, $args)) {
            Log::e(__CLASS__, "Error: APC Route Cache Failed. Had to fall back to CPath\\Router");
            RouteBuilder::rebuildAPCCache();
            return $Route;
        }
        return NULL;
    }
}