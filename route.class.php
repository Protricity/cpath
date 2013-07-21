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
        $mArgs = array(),
        $mCurArg = -1,
        $mRequest = NULL;

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

    public function getCurrentArg() {
        $cur = $this->mCurArg <= 0 ? 0 : $this->mCurArg;
        return $this->mArgs[$cur];
    }

    public function getNextArg() {
        if(!$this->hasNextArg())
            return NULL;
        $this->mCurArg++;
        return $this->mArgs[$this->mCurArg];
    }

    public function hasNextArg() {
        if($this->mCurArg >= sizeof($this->mArgs)-1) {
            return false;
        }
        return true;
    }

    public function addToRoute($path) {
        $this->mRoute .= '/' . $path;
        return $this;
    }

    /**
     * Returns the request parameters.
     * If none are set, returns the web request parameters depending on Content-Type and other factors
     * @return Array the request parameters
     */
    public function getRequest() {
        if($this->mRequest)
            return $this->mRequest;
        if(!$_POST && in_array(Util::getUrl('method'), array('GET', 'CLI'))) {                // if GET
            $request = $_GET;
        } else {                                                                        // else POST
            if(!$_POST && Util::getHeader('Content-Type') === 'application/json') {     // if JSON Object,
                $request = json_decode( file_get_contents('php://input'), true);        // Parse out json
            } else {
                $request = $_POST;                                                      // else use POST
            }
        }
        return $request;
    }

    public function setRequest(Array $request) {
        $this->mRequest = $request;
    }

    /**
     * Try's a route against a request path
     * @param string|null $requestPath the request path to match
     * @return bool whether or not the path matched
     * @throws DestinationNotFoundException if the destination handler was not found
     */
    public function match($requestPath) {
        if(strpos($requestPath, $this->mRoute) !== 0)
            return false;
        return true;
    }


    /**
     * Renders the route destination
     * @return void
     * @throws InvalidHandlerException if the destination handler was invalid
     */
    public function render($requestPath) {
        $argString = substr($requestPath, strlen($this->mRoute) + 1);
        $this->mArgs = array();
        if($argString)
            foreach(explode('/', $argString) as $arg)
                $this->mArgs[] = $arg;

        $dest = $this->mDestination;
        $Class = new \ReflectionClass($dest);
        if($Class->implementsInterface("Cpath\\Interfaces\\IHandlerAggregate")) {
            $Handler = call_user_func($dest."::getHandler");
            $Handler->render($this);
        } else if($Class->implementsInterface("Cpath\\Interfaces\\IHandler")) {
            $Handler = new $dest();
            $Handler->render($this);
        } else {
            throw new InvalidHandlerException("Destination '{$dest}' is not a valid IHandler or IHandlerAggregate");
        }
    }

    // Static methods

    /**
     * Loads all routes and attempts to match them to the request path
     * @throws NoRoutesFoundException if no routes matched
     */
    public static function tryAllRoutes($routePath=NULL, Array $request=NULL) {
        $routes = self::getRoutes();
        if($routePath===NULL) $routePath = Util::getUrl('route');
        if(preg_match('/\.\w+$/', $routePath)) {
            header("HTTP/1.0 404 File request was passed to Script");
            die();
        }
        foreach($routes as $route) {
            $Route = new Route($route[0], $route[1]);
            if(!$Route->match($routePath))
                continue;
            if($request)
                $Route->setRequest($request);
            $Route->render($routePath);
            return;
        }
        throw new NoRoutesFoundException("No Routes Matched: " . $routePath);
    }

    public static function getRoutes($method=NULL) {
        $routes = array();
        $path = Base::getGenPath().'routes.php';
        if(!file_exists($path) || !(include $path) || !$routes) {
            Build::buildClasses();
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
}