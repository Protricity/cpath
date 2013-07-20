<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath;
use CPath\Interfaces\IRoute;

/** Thrown when a valid route could not find a corresponding handler */
class DestinationNotFoundException extends \Exception {}
/** Thrown when a valid route's handler is invalid */
class InvalidHandlerException extends \Exception {}
/** Thrown when no valid routes could be found */
class NoRoutesFoundException extends \Exception {}

/**
 * Class Route - a route entry
 * @package CPath
 */
class Route implements IRoute {

    private
        $mRoute,
        $mDestination;
    private static
        $mCurrentRoute = NULL;

    /**
     * Constructs a new Route Entry
     * @param $route string the route request path
     * @param $destination string the handler class for this route
     */
    public function __construct($route, $destination) {
        $this->mRoute = $route;
        $this->mDestination = $destination;
    }

    public function getRoute() { return $this->mRoute; }
    public function getDestination() { return $this->mDestination; }

    /**
     * Try's a route against a request path
     * @param string|null $requestPath the request path to match
     * @return bool whether or not the path matched
     * @throws DestinationNotFoundException if the destination handler was not found
     * @throws InvalidHandlerException if the destination handler was invalid
     */
    public function tryRoute($requestPath) {
        if(strpos($requestPath, $this->mRoute) !== 0)
            return false;

        $argString = substr($requestPath, strlen($this->mRoute) + 1);
        if($argString)
            $args = explode('/', $argString);
        else
            $args = array();

        self::$mCurrentRoute = $this;

        $dest = $this->mDestination;
        $Class = new \ReflectionClass($dest);
        if($Class->implementsInterface("Cpath\\Interfaces\\IHandlerAggregate")) {
            $Handler = call_user_func($dest."::getHandler");
            $Handler->render($args);
        } else if($Class->implementsInterface("Cpath\\Interfaces\\IHandler")) {
            $Handler = new $dest();
            $Handler->render($args);
        } else {
            throw new InvalidHandlerException("Destination '{$dest}' is not a valid IHandler or IHandlerAggregate");
        }

        return true;
    }

    // Static methods

    /**
     * Loads all routes and attempts to match them to the request path
     * @throws NoRoutesFoundException if no routes matched
     */
    public static function tryAllRoutes() {
        $routes = array();
        $path = Base::getGenPath().'routes.php';
        if(!file_exists($path) || !(include $path) || !$routes) {
            Build::buildClasses();
            require $path;
        }
        $routePath = Util::getUrl('route');
        if(preg_match('/\.\w+$/', $routePath)) {
            header("HTTP/1.0 404 File request was passed to Script");
            die();
        }
        foreach($routes as $route) {
            $Route = new Route($route[0], $route[1]);
            if($Route->tryRoute($routePath))
                return;
        }
        throw new NoRoutesFoundException("No Routes Matched: " . Util::getUrl('route'));
    }

    /**
     * Get the currently used route.
     * @return IRoute the current route
     */
    public static function getCurrentRoute() {
        return self::$mCurrentRoute;
    }
}