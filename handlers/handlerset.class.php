<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Handlers;
use CPath\Build;
use CPath\BuildException;
use CPath\Builders\RouteBuilder;
use CPath\Interfaces\HandlerSetException;
use CPath\Interfaces\IBuildable;
use CPath\Interfaces\IHandler;
use CPath\Interfaces\IHandlerAggregate;
use CPath\Interfaces\IHandlerSet;
use CPath\Interfaces\IRequest;
use CPath\Interfaces\IRoute;
use CPath\Interfaces\IRouteBuilder;
use CPath\Model\Response;
use CPath\Model\Route;
use CPath\Util;

/**
 * Class HandlerSet
 * @package CPath\Handlers
 *
 * Provides a Handler Set for Handler calls
 */

class InvalidRouteException extends \Exception {}

class HandlerSet implements IHandlerSet {

    const Route_Methods = 'GET|POST|CLI';     // Default accepted methods are GET and POST
    const Route_Path = NULL;        // No custom route path. Path is based on namespace + class name

    /** @var IHandler[] */
    protected $mHandlers = array();
    protected $mSource;

    public function __construct(IHandlerAggregate $Source) {
        $this->mSource = $Source;
    }

    /**
     * Adds an IHandler to the set by route
     * @param String $route the route to the sub api i.e. POST (any POST), GET search (relative), GET /site/users/search (absolute)
     * @param IHandler $Handler the IHandler instance to add to the set
     * @param bool $replace if true, replace any existing handlers
     * @return HandlerSet self
     * @throws HandlerSetException
     */
    public function add($route, IHandler $Handler, $replace=false) {
        if(!$replace && isset($this->mHandlers[$route])) {
            throw new HandlerSetException("Route already exists: ".$route);
        } elseif($replace && !isset($this->mHandlers[$route])) {
            throw new HandlerSetException("Failed to replace Route: Does not exists: ".$route);
        }
        $this->mHandlers[$route] = $Handler;
        return $this;
    }

//    /**
//     * Adds an IHandler to the set by route
//     * @param String $route the route to the sub api i.e. POST (any POST), GET search (relative), GET /site/users/search (absolute)
//     * @param String $className the IHandler instance to add to the set
//     * @param bool $replace if true, replace any existing handlers
//     * @return HandlerSet self
//     * @throws HandlerSetException
//     */
//    public function addHandlerByClass($route, $className, $replace=false) {
//        if(!$replace && isset($this->mHandlers[$route])) {
//            throw new HandlerSetException("Route already exists: ".$route);
//        } elseif($replace && !isset($this->mHandlers[$route])) {
//            throw new HandlerSetException("Failed to replace Route: Does not exists: ".$route);
//        }
//        $this->mHandlers[$route] = $className;
//        return $this;
//    }

    /**
     * Returns an IHandler instance by route
     * @param String $route the route associated with this handler
     * @return IHandler
     * @throws InvalidRouteException if the route is not found
     */
    public function get($route) {
        if(!isset($this->mHandlers[$route]))
            throw new InvalidRouteException("Route '{$route}' is invalid. Possible routes are: ".implode(', ', array_keys($this->mHandlers)));
        $Handler = $this->mHandlers[$route];
        //if(is_string($Handler))
        //    return $this->mHandlers[$route] = new $Handler($this->mSource);
        return $Handler;
    }

    /**
     * Returns an IHandler instance by route
     * @param String $route the route associated with this handler
     * @return HandlerSet self
     * @throws InvalidRouteException if the route is not found
     */
    public function remove($route) {
        if(!isset($this->mHandlers[$route]))
            throw new InvalidRouteException("Route '{$route}' is invalid. Possible routes are: ".implode(', ', array_keys($this->mHandlers)));
        unset($this->mHandlers[$route]);
        return $this;
    }

    /**
     * Returns the source class that created this instance
     * @return IHandlerAggregate the source class name
     */
    public function getSource() {
        return $this->mSource;
    }

    /**
     * Render using the selected Handler in the set
     * @param IRequest $Request
     * @throws InvalidRouteException
     */
    function render(IRequest $Request) {
        $route = $Request->getNextArg();
        if(!$route)
            throw new InvalidRouteException("Route is missing. Possible routes are: ".implode(', ', array_keys($this->mHandlers)));
        $this->get($route)->render($Request);
    }

    /**
     * Returns an array of all routes for this class
     * @param IRouteBuilder $Builder the IRouteBuilder instance
     * @return IRoute[]
     * @throws \CPath\BuildException when a route is not in a valid format
     */
    function getAllRoutes(IRouteBuilder $Builder) {
        $Builder = new RouteBuilder();
        $Class = new \ReflectionClass($this->mSource);
        $defaultPath = $Builder->getHandlerDefaultPath($this->mSource);
        $routes = array();
        $regex = '/^('.IRoute::Methods.')( (\/)?(.*))?$/';
        foreach($this->mHandlers as $route => $Handler) {
            //if($Handler instanceof IRoutable) {
            //    $routes = array_merge($routes, $Handler->getAllRoutes($Builder));
            //} else {
            if(!preg_match($regex, $route, $matches))
                throw new BuildException("Route '$route' is not a valid route");
            if(empty($matches[4])) $path = $defaultPath;
            else $path = !empty($matches[3]) ? $matches[4] : $defaultPath . '/' .$matches[4];
            $routes[$route] = new Route($matches[1] . ' ' . $path, $Class->getName(), $route);
        }
        $routes['GET :api'] = new Route('GET ' . $defaultPath . '/:api', 'CPath\Handlers\Views\HandlerSetInfo', $Class->getName());
        return $routes;
    }
//
//    // Implement IHandlerAggregate
//
//    /**
//     * Returns an IHandler instance based on the currently selected Handler
//     * @param String $route an optional route to retrieve the IHandler instance from the specified route
//     * @return IHandler $Handler an IHandler instance
//     */
//    function getAggregateHandler($route=NULL)
//    {
//        return $this->getHandler($)
//    }

    // Implement ArrayAccess

    public function offsetExists($route) {
        try {
            $this->get($route);
            return true;
        } catch (InvalidRouteException $ex) {
            return false;
        }
    }

    /**
     * Shortcut for getAPI($path)
     * @param mixed $route
     * @return IHandler
     */
    public function offsetGet($route) { return $this->get($route);}
    public function offsetSet($route, $value) { $this->add($route, $value); }
    public function offsetUnset($route) { $this->remove($route); }

    // Implement IteratorAggregate

    public function getIterator() { return new \ArrayIterator($this->mHandlers); }

}
