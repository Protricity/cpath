<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Route;

use CPath\Compare\IComparable;
use CPath\Framework\Interfaces\Constructable\IConstructable;
use CPath\Framework\Render\Interfaces\IRender;
use CPath\Framework\Request\Interfaces\IRequest;
use CPath\Framework\Response\Exceptions\CodedException;


/** Thrown when a valid route could not find a corresponding handler */
class DestinationNotFoundException extends \Exception {}
/** Thrown when a valid route's handler is invalid */
class InvalidHandlerException extends \Exception {}
/** Thrown when no valid routes could be found */
class NoRoutesFoundException extends CodedException {
    const DEFAULT_CODE = 404;
}
class InvalidRouteException extends \Exception {}

interface IRoute extends IConstructable, IComparable
{

    const METHODS = 'GET,POST,PUT,PATCH,DELETE,CLI';

    /**
     * Try's a route against a request path and parse out any request args
     * @param string|null $requestPath the request path to match
     * @param Array &$args populated with args parsed out of the path
     * @return boolean return true if match is found
     */
    function match($requestPath, Array &$args=array());

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
     * @return \CPath\Framework\Render\Interfaces\IRender
     */
    function loadHandler();

    /**
     * Match the destination to the route and return an instance of the destination object
     * Note: this method should throw an exception if the requested route (method + path) didn't match
     * @param IRequest $Request the request to render
     * @return \CPath\Framework\Render\Interfaces\IRender
     * @throws InvalidRouteException if the requested route (method + path) didn't match
     */
    //function routeRequestToHandler(IRequest $Request);

}

