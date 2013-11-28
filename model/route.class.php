<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Model;
use CPath\Interfaces\IHandler;
use CPath\Interfaces\IHandlerAggregate;
use CPath\Interfaces\InvalidHandlerException;
use CPath\Interfaces\IRequest;
use CPath\Interfaces\IRoute;

/**
 * Class Route - a route entry
 * @package CPath
 */
class Route implements IRoute {

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

    public function getPrefix() { return $this->mPrefix; }
    public function getDestination() { return $this->mDestination; }

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
     * Get a list of arguments that the constructor calls to instantiate this instance
     * @return Array
     */
    function getExportArgs() {
        return array_merge(array($this->mPrefix, $this->mDestination), $this->mArgs);
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
     * Renders the route destination using an IRequest instance
     * @param IRequest $Request the request to render
     * @return void
     * @throws InvalidHandlerException if the destination handler was invalid
     */
    public function render(IRequest $Request) {
        $this->getHandler()
            ->render($Request);
    }
}