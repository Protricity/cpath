<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Model;
use CPath\Builders\RouteBuilder;
use CPath\Interfaces\IHandler;
use CPath\Interfaces\IHandlerAggregate;
use CPath\Interfaces\IRoute;
use CPath\Interfaces\InvalidHandlerException;
use CPath\Log;
use CPath\Util;

/**
 * Class Route - a route entry
 * @package CPath
 */
class Route extends ArrayObject implements IRoute {

    private
        $mRoute,
        $mDestination,
        $mArgs = array(),
        $mPos = 0,
        $mRequest = array(),
        $mBaseRequest = array();

    /**
     * Constructs a new Route Entry
     * @param $routePrefix string the route prefix
     * @param $destination string the handler class for this route
     * @param $_args string|array varargs list of strings for arguments or associative arrays for request fields
     */
    public function __construct($routePrefix, $destination, $_args=NULL) {
        $this->mRoute = $routePrefix;
        $this->mDestination = $destination;
        if($_args && $c = func_num_args())
            for($i=2; $i<$c; $i++)
                if($arg = func_get_arg($i))
                    if(is_array($arg)) $this->mBaseRequest = $arg;
                    else $this->mArgs[] = $arg;
    }

    public function getPrefix() { return $this->mRoute; }
    public function getDestination() { return $this->mDestination; }

    public function getNextArg() {
        return isset($this->mArgs[$this->mPos])
            ? $this->mArgs[$this->mPos++]
            : NULL;
    }

    /**
     * Returns the request parameters.
     * If none are set, returns the web request parameters depending on Content-Type and other factors
     * @return Array the request parameters
     */
    public function getRequest() {
        if($this->mRequest)
            return $this->mRequest;
        if(Util::isCLI())
            return Util::getCLI()->getRequest() + $this->mBaseRequest;
        if($_POST)
            return $_POST + $this->mBaseRequest;
        switch(Util::getUrl('method')) {
            case 'GET':
                return $_GET + $this->mBaseRequest;
            case 'POST':
            case 'PUT':
            case 'PATCH':
            case 'DELETE':
                $input = file_get_contents('php://input');
                if(Util::getHeader('Content-Type') === 'application/json')
                    return json_decode($input , true);
                parse_str($input, $request);
                return $request + $this->mBaseRequest;
            default:
                Log::e(__CLASS__, "Invalid Request Method: ".Util::getUrl('method'));
                return array();
        }
    }

    /**
     * Try's a route against a request path
     * @param string|null $requestPath the request path to match
     * @return bool whether or not the path matched
     */
    public function match($requestPath) {
        if(strpos($requestPath, $this->mRoute) !== 0)
            return false;

        if(strlen($requestPath) > ($c = strlen($this->mRoute))
            && substr($requestPath, $c, 1) != '/')
            return false;

        $argString = substr($requestPath, strlen($this->mRoute) + 1);
        //$this->mArgs = array();
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
        $Handler = new $dest;
        if($Handler instanceof IHandlerAggregate) {
            $Handler = $Handler->getAggregateHandler();
        } else if($Handler instanceof IHandler) {
        } else {
            throw new InvalidHandlerException("Destination '{$dest}' is not a valid IHandler or IHandlerAggregate");
        }
        return $Handler;
    }

    /**
     * Renders the route destination
     * @return void
     * @throws InvalidHandlerException if the destination handler was invalid
     */
    public function render() {
        $this->getHandler()
            ->render($this);
    }

    /**
     * Set the Request for this route to be used
     * @param $request Array the request for this route
     */
    function setRequest(Array $request) {
        $this->mRequest = $request + $this->mBaseRequest;
    }

    /**
     * Return a reference to this object's associative array
     * @return array the associative array
     */
    protected function &getArray() {
        return $this->mRequest;
    }
}