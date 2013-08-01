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
use CPath\Interfaces\IHandler;
use CPath\Interfaces\IHandlerSet;
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

    const Build_Ignore = true;     // This class should not be built. Classes that extend it should set Build_Ignore to true

    const Route_Methods = 'GET|POST|CLI';     // Default accepted methods are GET and POST
    const Route_Path = NULL;        // No custom route path. Path is based on namespace + class name

    /** @var IHandler[] */
    protected $mHandlers = array();
    protected $mSource;

    public function __construct($sourceClass) {
        $this->mSource = $sourceClass;
    }

    /**
     * Adds an IHandler to the set by route
     * @param String $route the route to the sub api i.e. POST (any POST), GET search (relative), GET /site/users/search (absolute)
     * @param IHandler $Handler the IHandler instance to add to the set
     * @param bool $replace if true, replace any existing handlers
     * @throws HandlerSetException
     */
    public function addHandler($route, IHandler $Handler, $replace=false) {
        if(!$replace && isset($this->mHandlers[$route])) {
            throw new HandlerSetException("Route already exists: ".$route);
        }
        $this->mHandlers[$route] = $Handler;
    }

    /**
     * Returns an IHandler instance by route
     * @param $route String the route associated with this handler
     * @return IHandler|NULL
     */
    public function getHandler($route) {
        return isset($this->mHandlers[$route]) ? $this->mHandlers[$route] : NULL;
    }

    function render(IRoute $Route)
    {
        $route = $Route->getNextArg();
        if(!$route)
            throw new InvalidRouteException("Route is missing. Possible routes are: ".implode(', ', array_keys($this->mHandlers)));
        if(!isset($this->mHandlers[$route]))
            throw new InvalidRouteException("Route '{$route}' is missing invalid. Possible routes are: ".implode(', ', array_keys($this->mHandlers)));
        $this->mHandlers[$route]->render($Route);
    }

    /**
     * Returns an array of all routes for this class
     * @param IRouteBuilder $Builder the IRouteBuilder instance
     * @return IRoute[]
     * @throws \CPath\BuildException when a route is not in a valid format
     */
    function getAllRoutes(IRouteBuilder $Builder) {
        $Builder = new RouteBuilder();
        $defaultPath = $Builder->getHandlerDefaultPath(new \ReflectionClass($this->mSource));
        $routes = array();
        $regex = '/^('.IRouteBuilder::METHODS.')( (\/)?(.*))?$/';
        foreach($this->mHandlers as $route => $Handler) {
            //if($Handler instanceof IRoutable) {
            //    $routes = array_merge($routes, $Handler->getAllRoutes($Builder));
            //} else {
            if(!preg_match($regex, $route, $matches))
                throw new BuildException("Route '$route' is not a valid route");
            if(empty($matches[4])) $path = $defaultPath;
            else $path = !empty($matches[3]) ? $matches[4] : $defaultPath . '/' .$matches[4];
            $routes[] = new Route($matches[1] . ' ' . $path, $this->mSource, $route);
        }
        //if(!isset($routes['GET']))
            $routes['GET api'] = new Route('GET ' . $defaultPath . '/:api', 'CPath\Handlers\Views\HandlerSetInfo', $this->mSource);
        return $routes;
    }

    // Implement ArrayAccess

    public function offsetExists($route) {
        try {
            $this->getHandler($route);
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
    public function offsetGet($route) { return $this->getHandler($route);}
    public function offsetSet($route, $value) { $this->addHandler($route, $value); }
    public function offsetUnset($route) { unset($this->mHandlers[$route]); }

    // Implement IteratorAggregate

    public function getIterator() { return new \ArrayIterator($this->mHandlers); }
}
