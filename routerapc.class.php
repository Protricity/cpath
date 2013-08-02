<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath;
use CPath\Builders\RouteBuilder;
use CPath\Interfaces\IRoute;
use CPath\Model\FileRequestRoute;

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
     * @return IRoute|NULL the found route or null if none found
     */
    public static function findRoute($routePath) {
        if(($inc = Base::getConfig('build.inc')) && ($inc != apc_fetch(self::PREFIX . '.inc'))) {
            RouteBuilder::rebuildAPCCache();
            apc_store(self::PREFIX . '.inc', $inc);
        }
        $route = $routePath;
        if(($max = Base::getConfig('route.max', 0)) && ($max < strlen($route))) {
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
            if(!$Route->match($routePath)) {
                Log::e(__CLASS__, "APC Cache did not match route: ". $route);
                apc_delete($apcRoute);
                continue;
            }
            return $Route;
        }
        if($Route = Router::findRoute($routePath)) {
            Log::e(__CLASS__, "Error: APC Route Cache Failed. Had to fall back to CPath\\Router");
            RouteBuilder::rebuildAPCCache();
            return $Route;
        }
        return NULL;
    }
}