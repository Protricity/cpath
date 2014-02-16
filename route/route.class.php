<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Route;
use CPath\Compare\IComparable;
use CPath\Compare\IComparator;
use CPath\Framework\Render\Interfaces\IRender;
use CPath\Interfaces\IBuildable;

/**
 * Class Route - a route entry
 * @package CPath
 */
class Route implements IRoute {

    private
        $mPrefix,
        $mDestination,
        $mHandlerInst=null,
        $mArgs = array();

    /**
     * Constructs a new Route Entry
     * @param $routePrefix string the route prefix
     * @param $destination string|\CPath\Framework\Render\Interfaces\IRender the handler class for this route
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
     * @return \CPath\Framework\Render\Interfaces\IRender
     */
    function loadHandler() {
        /** @var IBuildable $dest */
        $dest = $this->getDestination();
        $Handler = $this->mHandlerInst ?: $dest::createBuildableInstance();

        if(!$Handler instanceof IRender)
            throw new InvalidHandlerException("Destination '{$dest}' is not a valid IRender");

        return $Handler;
    }

    /**
     * Try's a route against a request path and parse out any request args
     * @param string|null $requestPath the request path to match
     * @param Array &$args populated with args parsed out of the path
     * @return boolean return true if match is found
     */
    function match($requestPath, Array &$args=array()) {
        if(substr_compare($this->mPrefix, 'ANY', 0, 3, true) === 0) {
            list(, $path) = explode(' ', $requestPath, 2);
            $prefix = substr($this->mPrefix, 4);
            if(strpos($path, $prefix) !== 0)
                return false;
        } else {
            if(strpos($requestPath, $this->mPrefix) !== 0)
                return false;
        }

        //if(strlen($requestPath) > ($c = strlen($this->mPrefix))
        //    && substr($requestPath, $c, 1) != '/')
        //    return false;
        // TODO: remove?

        $argString = substr($requestPath, strlen($this->mPrefix) + 1);
        $args2 = $this->mArgs;
        if($argString)
            foreach(explode('/', $argString) as $arg)
                if($arg) $args2[] = $arg;

        $args = $args2;
        return true;
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

    /**
     * Determine if two IRoute objects are equal
     * @param IComparable|IRoute $obj the object to compare against $this
     * @param \CPath\Compare\IComparator $C the IComparator instance
     * @return integer < 0 if $this is less than $obj; > 0 if $this is greater than $obj, and 0 if they are equal.
     */
    function compareTo(IComparable $obj, IComparator $C)
    {
        $C->compareScalar($this->getPrefix(), $obj->getPrefix());
        $C->compareScalar($this->getDestination(), $obj->getDestination());
    }

    // Static

    /**
     * Creates a new Route for an IRender
     * @param IRender $Handler The class instance or class name
     * @param String $method the route prefix method (GET, POST...) for this IRender
     * @param String $path a custom path for this IRender
     * @return Route
     */
    static final function fromHandler(IRender $Handler, $method='ANY', $path=NULL) {
        $prefix = RoutableSet::getPrefixFromHandler($Handler, $method, $path);
        return new static($prefix, get_class($Handler));
    }

    /**
     * Gets the default public route path for this handler
     * @param \CPath\Framework\Render\Interfaces\IRender $Handler The class instance or class name
     * @param String $method the route prefix method (GET, POST...) for this IRender
     * @param String $path a custom path for this IRender
     * @return RoutableSet
     */
    static final function getPrefixFromHandler(IRender $Handler, $method='ANY', $path=NULL) {
        $cls = get_class($Handler);
        if(!$path)
            $path = str_replace('\\', '/', strtolower($cls));
        if($path[0] !== '/')
            $path = '/' . $path;
        return $method . ' ' . $path;
    }
}