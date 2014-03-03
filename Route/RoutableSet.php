<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Route;
use CPath\Framework\Data\Compare\IComparable;
use CPath\Framework\Data\Compare\Util\CompareUtil;
use CPath\Framework\Render\IRender;
use CPath\Framework\Request\Interfaces\IRequest;
use CPath\Log;
use Traversable;

/**
 * Class Route - a route entry
 * @package CPath
 */
class RoutableSet implements IRoute, \ArrayAccess, \IteratorAggregate {

    const PATH_DEFAULT = "#Default";

    private
        $mPrefix,
        $mRoutes = array(),
        $mDestination,
        $mHandlerInst=null,
        $mArgs = array();

    /** @var IRoute */
    private
        $mUsedRoute = null;

    /**
     * Constructs a new Route Entry
     * @param $routePrefix string the route prefix
     * @param $destination string|\CPath\Framework\Render\IRender the handler class for this route
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
     * @return \CPath\Framework\Render\IRender
     */
    function loadHandler() {
        /** @var \CPath\Framework\Build\IBuildable $dest */
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
     * @throws InvalidHandlerException
     */
    function match($requestPath, Array &$args=array()) {
        list($method, $path) = explode(' ', $requestPath, 2);
        if(substr_compare($this->mPrefix, 'ANY', 0, 3, true) === 0) {
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

        $args2 = $this->mArgs;
        /** @var \CPath\Framework\Build\IBuildable $dest */
        $dest = $this->getDestination();
        $Handler = $this->mHandlerInst ?: $dest::createBuildableInstance();

        if(!$Handler instanceof IRender)
            throw new InvalidHandlerException("Destination '{$dest}' is not a valid IRender");

        if($Handler instanceof IRoutable) {
            $this->mUsedRoute = $Route = $Handler->loadRoute();
            if($Route instanceof RoutableSet) {
                // TODO: refactor/ugly.
                $this->mRoutes = $Route->mRoutes;
                uasort($this->mRoutes, function (IRoute $a, IRoute $b){
                    $b = $b->getPrefix();
                    $a = $a->getPrefix();
                    return (substr_count($b, '/')-substr_count($a, '/'));
                });

                /** @var IRoute $SubRoute */
                foreach($this->mRoutes as $short => $SubRoute) {
                    if(!($SubRoute instanceof RoutableSet) // TODO: Fix hack
                        && $short != self::PATH_DEFAULT // TODO: Fix hack
                        && $SubRoute->match($requestPath, $args2)) {
                        $this->mPrefix = $SubRoute->getPrefix();
                        $this->mDestination = $SubRoute->getDestination();
                        $this->mHandlerInst = $SubRoute->loadHandler();
                        $args = $args2;
                        return true;
                    }
                }

                if($this->hasDefault($method)) {
                    $SubRoute = $this->getDefault($method);
                    $this->mPrefix = $SubRoute->getPrefix();
                    $this->mDestination = $SubRoute->getDestination();
                    $this->mHandlerInst = $SubRoute->loadHandler();
                    $args = $args2;
                    return true;
                }
                Log::e(__CLASS__, "Sub route could not be found: " . $requestPath);
                //return false;
                throw new InvalidRouteException("Sub route could not be found: " . $requestPath);
            }
        }

        $argString = substr($requestPath, strlen($this->mPrefix) + 1);
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

    function getRoutes() {
        return $this->mRoutes;
    }

    function add($prefix, IRender $Handler, $replace=false) {
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

        //if($Route instanceof AbstractRoute)
        //    $Route->setPrefix($newPrefix);

        if(isset($this->mRoutes[$prefix]) && !$replace)
            throw new \InvalidArgumentException("Routable Prefix already exists: " . $prefix);

        $this->mRoutes[$prefix] = $Route;
    }

    /**
     * Returns a default route for a prefix or a default route for all prefixes
     * @param string $prefix the request method to used by default or ANY for all prefixes
     * @return IRoute;
     */
    function getDefault($prefix='GET') {
        $req = $prefix. ' ' . static::PATH_DEFAULT;
        $aReq = 'ALL ' . static::PATH_DEFAULT;
        return
            isset($this->mRoutes[$req]) ? $this->mRoutes[$req] :
            isset($this->mRoutes[$aReq]) ? $this->mRoutes[$aReq] :
            $this->mRoutes[$req]; // Throw notice
    }

    /**
     * Checks for the existance of a default route or default for all routes
     * @param string $prefix the request method to used by default or ANY for all prefixes
     * @return bool
     */
    function hasDefault($prefix='GET') {
        return isset($this->mRoutes[$prefix. ' ' . static::PATH_DEFAULT])
            || ($prefix === 'ALL' && $this->hasDefault('ALL'));
    }

    function setDefault(IRoute $Route, $prefix='GET', $replace=false) {
        $this->addRoute($prefix. ' ' . static::PATH_DEFAULT, $Route, $replace);
    }

    /**
     * Renders the route destination using an IRequest instance
     * @param IRequest $Request the request to render
     * @return void
     * @throws InvalidHandlerException if the destination handler was invalid
     */
    public function renderSet(IRequest $Request) {
        if($this->mUsedRoute) {
            $Handler = $this->mUsedRoute->loadHandler();
            $Wrapper = new RoutableSetWrapper($Request, $this, $this->mUsedRoute);
            $Handler->render($Wrapper);
        } else {
            $Handler = $this->loadHandler();
            $Handler->render($Request);
        }
    }

    /**
     * Compare two objects
     * @param IComparable $obj the object to compare against $this
     * @return integer < 0 if $obj is less than $this; > 0 if $obj is greater than $this, and 0 if they are equal.
     */
    function compareTo(IComparable $obj)
    {
        if(!$obj instanceof RoutableSet)
            return 1;

        $Util = new CompareUtil();
        return
            $Util->compareScalar($this->getPrefix(), $obj->getPrefix())
            + $Util->compareScalar($this->getDestination(), $obj->getDestination());
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
     * @param \CPath\Framework\Render\IRender|IRoute $value <p>
     * The value to set.
     * </p>
     * @return void
     */
    public function offsetSet($offset, $value) {
        if($value instanceof IRender)
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
     * Creates a new RouteSet for an IRender with multiple routes
     * @param \CPath\Framework\Render\IRender $Handler The class instance or class name
     * @param String $method the route prefix method (GET, POST...) for this IRender
     * @param String $path a custom path for this IRender
     * @return RoutableSet
     */
    static final function fromHandler(IRender $Handler, $method='ANY', $path=NULL) {
        $prefix = RoutableSet::getPrefixFromHandler($Handler, $method, $path);
        return new static($prefix, get_class($Handler));
    }

    /**
     * Gets the default public route path for this handler
     * @param IRender $Handler The class instance or class name
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