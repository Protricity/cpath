<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Route;
use CPath\Interfaces\IHandler;
use CPath\Interfaces\IRequest;
use Traversable;

/**
 * Class Route - a route entry
 * @package CPath
 */
class RoutableSet extends AbstractRoute implements \ArrayAccess, \IteratorAggregate {

    const PREFIX_DEFAULT = "#Default";

    private
        $mRoutes = array();
        //$mHandlers = array();

//    /**
//     * Constructs a new Route Entry
//     * @param $routePrefix string the route prefix
//     * @param $destination string the handler class for this route
//     * @param $_args string|array varargs list of strings for arguments or associative arrays for request fields
//     */
//    public function __construct($routePrefix, $destination, $_args=NULL) {
//        $this->mPrefix = $routePrefix;
//        $this->mDestination = $destination;
//        if($_args)
//            $this->mArgs = array_slice(func_get_args(), 2);
//    }

    function add($prefix, IHandler $Handler, $replace=false) {
        //$Route = $Handler->loadRoute();
        //if($prefix === null)
        //    $prefix = $Route->getPrefix(); // TODO: good idea?
        $method = $prefix;
        if(strpos($method, ' ') === false)
            $path = '';
        else
            list($method, $path) = explode(' ', $method, 2);
        list(, $basePath) = explode(' ', $this->getPrefix(), 2);
        $newPrefix = $method . ' ' . $basePath;
        if($path)
            $newPrefix .= '/' . $path;
        $Route = new Route($newPrefix, $Handler);
        $this->addRoute($prefix, $Route, $replace);
    }

    function addRoute($prefix, IRoute $Route, $replace=false) {
        if(isset($this->mRoutes[$prefix]) && !$replace)
            throw new \InvalidArgumentException("Routable Prefix already exists: " . $prefix);
        $this->mRoutes[$prefix] = $Route;
    }

    /** * @return IRoute */
    function getDefault() { return $this->mRoutes[static::PREFIX_DEFAULT]; }
    /** * @return bool */
    function hasDefault() { return isset($this->mRoutes[static::PREFIX_DEFAULT]); }
    //function setDefault(IHandler $Handler, $replace=false) { $this->add(static::PREFIX_DEFAULT, $Handler, $replace); }
    function setDefault(IRoute $Route, $replace=false) { $this->addRoute(static::PREFIX_DEFAULT, $Route, $replace); }

    /**
     * Renders the route destination using an IRequest instance
     * @param IRequest $Request the request to render
     * @return void
     * @throws InvalidHandlerException if the destination handler was invalid
     */
    public function renderSet(IRequest $Request) {
        $Route = $this->findRequestRoute($Request);
        $Handler = $Route->loadHandler();
        $Handler->render($Request);
    }

    /**
     * Match the destination to the route and return an instance of the destination object
     * Note: this method should throw an exception if the requested route (method + path) didn't match
     * Note: if successful, this method should consume one argument from the IRequest
     * @param IRequest $Request the request to render
     * @return IRoute the found route instance
     * @throws InvalidRouteException if the requested route (method + path) didn't match
     */
    function findRequestRoute(IRequest $Request) {
        $path = $Request->getNextArg(false);
        $Route = $this->findRoute($path, $Request->getMethod());
        $Request->getNextArg(true);
        return $Route;
    }

    /**
     * Find an IRoute in the RouteSet.
     * @param $path
     * @param $method
     * @return IRoute the found route instance
     * @throws InvalidRouteException if the requested route (method + path) didn't match
     */
    function findRoute($path, $method) {
        $route = $method;
        if($path)
            $route .= ' ' . $path;

        if(isset($this->mRoutes[$route])) {
            return $this->mRoutes[$route];
        } else {
            while($route) {
                if(isset($this->mRoutes[$route]))
                    return $this->mRoutes[$route];
                if($p = strrpos($route, '/'))
                    $route = substr($route, 0, $p);
                elseif(strpos($route, ' '))
                    $route = $method;
                else
                    break;
            }
            if(isset($this->mRoutes[static::PREFIX_DEFAULT]))
                return $this->mRoutes[static::PREFIX_DEFAULT];

            //list($m, $p) = explode(' ', $this->getPrefix(), 2);
            //if(in_array($m, array('ANY', $Request->getMethod())))
            //    return $this->getHandler();
            //return $this->getHandler();
            throw new InvalidRouteException("Routable could not be found: " . var_export($route, true));
        }
    }


    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Retrieve an external iterator
     * @link http://php.net/manual/en/iteratoraggregate.getiterator.php
     * @return Traversable An instance of an object implementing <b>Iterator</b> or
     * <b>Traversable</b>
     */
    public function getIterator() {
        return new \ArrayIterator($this->mRoutes);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Whether a offset exists
     * @link http://php.net/manual/en/arrayaccess.offsetexists.php
     * @param mixed $offset <p>
     * An offset to check for.
     * </p>
     * @return boolean true on success or false on failure.
     * </p>
     * <p>
     * The return value will be casted to boolean if non-boolean was returned.
     */
    public function offsetExists($offset) {
        return isset($this->mRoutes[$offset]);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to retrieve
     * @link http://php.net/manual/en/arrayaccess.offsetget.php
     * @param mixed $offset <p>
     * The offset to retrieve.
     * </p>
     * @return IRoute .
     */
    public function offsetGet($offset) {
        return $this->mRoutes[$offset];
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to set
     * @link http://php.net/manual/en/arrayaccess.offsetset.php
     * @param mixed $offset <p>
     * The offset to assign the value to.
     * </p>
     * @param IHandler|IRoute $value <p>
     * The value to set.
     * </p>
     * @return void
     */
    public function offsetSet($offset, $value) {
        if($value instanceof IHandler)
            $this->add($offset, $value, false);
        else
            $this->addRoute($offset, $value, false);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to unset
     * @link http://php.net/manual/en/arrayaccess.offsetunset.php
     * @param mixed $offset <p>
     * The offset to unset.
     * </p>
     * @return void
     */
    public function offsetUnset($offset) {
        unset($this->mRoutes[$offset]);
    }

    // Static

    /**
     * Creates a new RouteSet for an IHandler with multiple routes
     * @param IHandler $Handler The class instance or class name
     * @param String $method the route prefix method (GET, POST...) for this IHandler
     * @param String $path a custom path for this IHandler
     * @return RoutableSet
     */
    static final function fromHandler(IHandler $Handler, $method='ANY', $path=NULL) {
        $prefix = RoutableSet::getPrefixFromHandler($Handler, $method, $path);
        return new static($prefix, get_class($Handler));
    }
}