<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath;
use CPath\Builders\BuildRoutes;
use CPath\Interfaces\IHandler;
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

    const APC_PREFIX = 'cpath.route.%s:';

    private
        $mRoute,
        $mDestination,
        $mArgs = array(),
        $mCurArg = -1,
        $mRequest = array();

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

    /**
     * Try's a route against a request path
     * @param string|null $requestPath the request path to match
     * @return bool whether or not the path matched
     * @throws DestinationNotFoundException if the destination handler was not found
     */
    public function match($requestPath) {
        if(strpos($requestPath, $this->mRoute) !== 0)
            return false;

        if(strlen($requestPath) > ($c = strlen($this->mRoute))
            && substr($requestPath, $c, 1) != '/')
            return false;

        $argString = substr($requestPath, strlen($this->mRoute) + 1);
        $this->mArgs = array();
        if($argString)
            foreach(explode('/', $argString) as $arg)
                if($arg) $this->mArgs[] = $arg;

        return true;
    }

    /**
     * Renders the route destination
     * @return IHandler
     * @throws InvalidHandlerException if the destination handler was invalid
     */
    public function getHandler() {

        $dest = $this->mDestination;
        $Class = new \ReflectionClass($dest);
        if($Class->implementsInterface("Cpath\\Interfaces\\IHandlerAggregate")) {
            $Handler = call_user_func($dest."::getHandler");
        } else if($Class->implementsInterface("Cpath\\Interfaces\\IHandler")) {
            $Handler = new $dest();
        } else {
            throw new InvalidHandlerException("Destination '{$dest}' is not a valid IHandler or IHandlerAggregate");
        }
        return $Handler;
    }

    /**
     * Renders the route destination
     * @param array $request optional request parameters
     * @return void
     * @throws InvalidHandlerException if the destination handler was invalid
     */
    public function render(Array $request=NULL) {
        if($request)
            $this->mRequest = $request + $this->mRequest;
        $this->getHandler()
            ->render($this);
    }

    // Static methods

    /**
     * Loads all routes and attempts to match them to the request path
     * @throws NoRoutesFoundException if no routes matched
     */
    public static function findRoute($routePath) {

        if(preg_match('/\.\w+$/', $routePath)) {
            header("HTTP/1.0 404 File request was passed to Script");
            die();
        }

        if(Base::isApcEnabled()) {
            $prefix = sprintf(self::APC_PREFIX, Base::getConfig('build.inc', 0));
            $route = $routePath;
            while(true) {
                $dest = apc_fetch($prefix.$route, $found);
                if(!$found) {
                    $p = strrpos($route, '/');
                    if(!$p) break;
                    $route = substr($route, 0, $p);
                    continue;
                }
                $Route = new Route($route, $dest[0]);
                if(!$Route->match($routePath)) {
                    Log::e(__CLASS__, "APC Cache did not match route: ". $route);
                    apc_delete($prefix.$route);
                    continue;
                }
                if(isset($dest[1]))
                    $Route->mRequest = (array)$dest[1] + $Route->mRequest;
                //if(isset($dest[1]))
                //    $request += (array)$dest[1];
                //if($request)
                //    $Route->setRequest($request);
                //$Route->render($routePath);
                return $Route;
            }
        }

        $routes = self::getRoutes();
        foreach($routes as $route) {
            $Route = new Route($route[0], $route[1]);
            if(!$Route->match($routePath))
                continue;
            if(isset($route[2]))
                $Route->mRequest = (array)$route[2] + $Route->mRequest;

            if(Base::isApcEnabled())
                apc_store(sprintf(self::APC_PREFIX, Base::getConfig('build.inc', 0)).$route[0], array_slice($route, 1));
            return $Route;
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