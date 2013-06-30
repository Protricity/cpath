<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Builders;
use CPath\BuildException;
use CPath\Base;
use CPath\Log;


/**
 * Class BuildHandlers
 * @package CPath\Builders
 *
 * Provides building methods for Handler Classes.
 * Builds [gen]/routes.php
 */
class BuildRoutes {
    const IHandler = "CPath\\Interfaces\\IHandler";
    const METHODS = 'GET|POST|PUT|DELETE|CLI';
    private static $mRoutes = array();

    /**
     * Builds a route for the specified IHandler class
     * @param \ReflectionClass $Class
     * @throws \CPath\BuildException
     */
    public static function build(\ReflectionClass $Class) {
        if(!$Class->implementsInterface(self::IHandler))
            throw new BuildException("Class ".$Class->getName()." does not implement ".self::IHandler);

        foreach(static::getHandlerRoutes($Class) as $route) {
            self::$mRoutes[] = array(
                'match' => $route,
                'class' => $Class->getName(),
            );
        }
    }

    /**
     * Checks to see if any routes were built, and builds them into [gen]/routes.php
     * @param \ReflectionClass $Class
     */
    public static function buildComplete(\ReflectionClass $Class) {
        if(!self::$mRoutes)
            return;

        $output = "<?php\n\$routes = array(";
        foreach(self::$mRoutes as $route)
            $output .= "\n\tarray('" . $route['match'] . "', '" . $route['class'] . "'),";
        $output .= "\n);";
        file_put_contents(Base::getGenPath().'routes.php', $output);
        Log::v(__CLASS__, count(self::$mRoutes) . " Route(s) rebuilt.");

        self::$mRoutes = array();
    }

    /**
     * Determines the Handler route(s) from constants or the class name
     * @param \ReflectionClass $Class
     * @return array a list of routes
     */
    public static function getHandlerRoutes(\ReflectionClass $Class) {
        $routes = array();
        $methods = $Class->getConstant('ROUTE_METHODS') ?: 'ALL';
        $route = $Class->getConstant('ROUTE_PATH');
        if(!$route) {
            $route = '/'.str_replace('\\', '/', strtolower($Class->getName()));
        }
        if(preg_match('/^([A-Z|]+) (.*)$/i', $route, $matches)) {
            list($full, $methods, $route) = $matches;
        }

        $allowed = explode('|', self::METHODS);
        $methods = explode('|', $methods);
        foreach($methods as &$method) {
            $method = strtoupper($method);
            if($method == 'ALL') {
                $methods = $allowed;
                break;
            }
            if(!in_array($method, $allowed))
                throw new BuildException("Method '{$method}' is not supported");
        }

        foreach($methods as $m) {
            $routes[] = $m . ' ' . $route;
        }

        return $routes;
    }
}
