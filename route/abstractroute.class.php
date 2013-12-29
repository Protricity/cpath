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

/**
 * Class Route - a route entry
 * @package CPath
 */
abstract class AbstractRoute implements IRoute {

    private
        $mPrefix,
        $mDestination,
        $mHandlerInst=null,
        $mArgs = array();

    /**
     * Constructs a new Route Entry
     * @param $routePrefix string the route prefix
     * @param $destination string|IHandler the handler class for this route
     * @param $_args string|array varargs list of strings for arguments or associative arrays for request fields
     */
    final function __construct($routePrefix, $destination, $_args=NULL) {
        $this->mPrefix = $routePrefix;
        
        if(is_object($destination)) {
            $this->mHandlerInst = $destination;
            $this->mDestination = get_class($destination);
        } else {
            $this->mDestination = $destination;
        }
        
        if($_args)
            $this->mArgs = array_slice(func_get_args(), 2);
    }

    protected function setPrefix($prefix) { $this->mPrefix = $prefix; }

    /**
     * Get the Route Prefix ("[method] [path]" or just "[method]")
     * @return mixed
     */
    final function getPrefix() { return $this->mPrefix; }
    
    /**
     * Get the Route Destination class or asset
     * @return String
     */
    final function getDestination() { return $this->mDestination; }

    /**
     * Get a buildable instance of the route destination
     * @throws InvalidHandlerException
     * @return IHandler
     */
    final function loadHandler() {
        /** @var IBuildable $dest */
        $dest = $this->getDestination();
        $Handler = $this->mHandlerInst ?: $dest::createBuildableInstance();

        if(!$Handler instanceof IHandler)
            throw new InvalidHandlerException("Destination '{$dest}' is not a valid IHandler");

        return $Handler;
    }

    /**
     * Try's a route against a request path and parse out any request args
     * @param string|null $requestPath the request path to match
     * @return array|boolean return all parsed request args or false if no match is found
     */
    final function match($requestPath) {
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
     * Exports constructor parameters for code generation
     * @return Array constructor params for var_export
     */
    final function exportConstructorArgs() {
        $dest = $this->mDestination;
        $dest = is_object($dest) ? get_class($dest) : $dest;
        $args = array_merge(array($this->mPrefix, $dest), $this->mArgs);
        return $args;
    }

    // Static

    /**
     * Gets the default public route path for this handler
     * @param IHandler $Handler The class instance or class name
     * @param String $method the route prefix method (GET, POST...) for this IHandler
     * @param String $path a custom path for this IHandler
     * @return RoutableSet
     */
    static final function getPrefixFromHandler(IHandler $Handler, $method='ANY', $path=NULL) {
        $cls = get_class($Handler);
        if(!$path)
            $path = str_replace('\\', '/', strtolower($cls));
        if($path[0] !== '/')
            $path = '/' . $path;
        return $method . ' ' . $path;
    }
}