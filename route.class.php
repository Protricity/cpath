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
        $mDestination,
        $mBaseUrl,
        $mArgs;

    /**
     * Constructs a new Route Entry
     * @param $route string the route request path
     * @param $destination string the handler class for this route
     */
    public function __construct($route, $destination) {
        $this->mRoute = $route;
        $this->mDestination = $destination;
    }

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

        $dest = $this->mDestination;
        if(class_exists($dest)) {
            $Handler = new $dest();
            if(!($Handler instanceof Interfaces\IHandler))
                throw new InvalidHandlerException("Destination '{$dest}' is not a valid IHandler");
        } else {
            throw new DestinationNotFoundException("Destination {$dest} could not be found");
        }

        $Handler->render($args);
        return true;
    }

    // Static methods

    /**
     * Loads all routes and attempts to match them to the request path
     * @throws NoRoutesFoundException if no routes matched
     */
    public static function tryAllRoutes() {
        $routes = array();
        include Base::getGenPath().'routes.php';
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
}