<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Route;
use CPath\Config;
use CPath\Interfaces\IBuildable;
use CPath\Interfaces\IHandler;
use CPath\Interfaces\IRequest;
use CPath\Serializer\ISerializable;
use CPath\Serializer\Serializer;

/**
 * Class Route - a route entry
 * @package CPath
 */
abstract class AbstractRoute implements IRoute {

    private
        $mPrefix,
        $mDestination,
        $mArgs = array();

    /**
     * Constructs a new Route Entry
     * @param $routePrefix string the route prefix
     * @param $destination string the handler class for this route
     * @param $_args string|array varargs list of strings for arguments or associative arrays for request fields
     */
    public function __construct($routePrefix, $destination, $_args=NULL) {
        $this->mPrefix = $routePrefix;
        $this->mDestination = $destination;
        if($_args)
            $this->mArgs = array_slice(func_get_args(), 2);
    }

    function getPrefix() { return $this->mPrefix; }
    function getDestination() { return $this->mDestination; }

    /**
     * Get a buildable instance of the route destination
     * @throws InvalidHandlerException
     * @internal param \CPath\Interfaces\IRequest $Request
     * @internal param \CPath\Interfaces\IRequest $Request the request to render
     * @return IHandler
     */
    function getHandler() {
        /** @var IBuildable $dest */
        $dest = $this->getDestination();
        $Handler = $dest::createBuildableInstance();

        if(!$Handler instanceof IHandler)
            throw new InvalidHandlerException("Destination '{$dest}' is not a valid IHandler");

        return $Handler;
    }

    /**
     * Try's a route against a request path and parse out any request args
     * @param string|null $requestPath the request path to match
     * @return array|boolean return all parsed request args or false if no match is found
     */
    public function match($requestPath) {
        if(strpos($requestPath, $this->mPrefix) !== 0)
            return false;

        if(strlen($requestPath) > ($c = strlen($this->mPrefix))
            && substr($requestPath, $c, 1) != '/')
            return false;

        $argString = substr($requestPath, strlen($this->mPrefix) + 1);
        $args = $this->mArgs;
        if($argString)
            foreach(explode('/', $argString) as $arg)
                if($arg) $args[] = $arg;

        return $args;
    }

    /**
     * Renders the route destination using an IRequest instance
     * @param IRequest $Request the request to render
     * @return void
     * @throws InvalidHandlerException if the destination handler was invalid
     */
    public function renderDestination(IRequest $Request) {
        $Handler = $this->getHandler();
        $Handler->render($Request);
    }

    // Static

    /**
     * Gets the default public route path for this handler
     * @param IHandler $Handler The class instance or class name
     * @param String $method the route prefix method (GET, POST...) for this IHandler
     * @param String $path a custom path for this IHandler
     * @return RouteSet
     */
    static function getPrefixFromHandler(IHandler $Handler, $method='ANY', $path=NULL) {
        $cls = get_class($Handler);
        if(!$path)
            $path = str_replace('\\', '/', strtolower($cls));
        if($path[0] !== '/')
            $path = '/' . $path;
        return $method . ' ' . $path;
    }

    /**
     * Creates a new RouteSet for an IHandler with multiple routes
     * @param IHandler $Handler The class instance or class name
     * @param String $method the route prefix method (GET, POST...) for this IHandler
     * @param String $path a custom path for this IHandler
     * @return RouteSet
     */
    static function fromHandler(IHandler $Handler, $method='ANY', $path=NULL) {
        $prefix = RouteSet::getPrefixFromHandler($Handler, $method, $path);
        return new static($prefix, get_class($Handler));
    }

    /**
     * Exports constructor parameters for code generation
     * @return Array constructor params for var_export
     */
    function exportConstructorArgs() {
        $args = array_merge(array($this->mPrefix, $this->mDestination), $this->mArgs);
        return $args;
    }
}