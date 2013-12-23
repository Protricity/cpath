<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Route;

use CPath\Constructable\IConstructable;
use CPath\Interfaces\IHandler;
use CPath\Interfaces\IRequest;


/** Thrown when a valid route could not find a corresponding handler */
class DestinationNotFoundException extends \Exception {}
/** Thrown when a valid route's handler is invalid */
class InvalidHandlerException extends \Exception {}
/** Thrown when no valid routes could be found */
class NoRoutesFoundException extends \Exception {}
class InvalidRouteException extends \Exception {}

interface IRoute extends IConstructable
{

    const METHODS = 'GET,POST,PUT,PATCH,DELETE,CLI';

    /**
     * Try's a route against a request path and parse out any request args
     * @param string|null $requestPath the request path to match
     * @return array|boolean return all parsed request args or false if no match is found
     */
    function match($requestPath);

    /**
     * Get the Route Prefix ("[method] [path]")
     * @return mixed
     */
    function getPrefix();

    /**
     * Get the Route Destination class or asset
     * @return String
     */
    function getDestination();

    /**
     * Load a buildable instance of the route destination
     * @return IHandler
     */
    function loadHandler();

    /**
     * Match the destination to the route and return an instance of the destination object
     * Note: this method should throw an exception if the requested route (method + path) didn't match
     * @param IRequest $Request the request to render
     * @return IHandler
     * @throws InvalidRouteException if the requested route (method + path) didn't match
     */
    //function routeRequestToHandler(IRequest $Request);

}

