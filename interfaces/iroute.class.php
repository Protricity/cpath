<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Interfaces;

/** Thrown when a valid route could not find a corresponding handler */
class DestinationNotFoundException extends \Exception {}
/** Thrown when a valid route's handler is invalid */
class InvalidHandlerException extends \Exception {}
/** Thrown when no valid routes could be found */
class NoRoutesFoundException extends \Exception {}

interface IRoute {

    const METHODS = 'GET|POST|PUT|PATCH|DELETE|CLI';

    /**
     * Try's a route against a request path and parse out any request args
     * @param string|null $requestPath the request path to match
     * @return array|boolean return all parsed request args or false if no match is found
     */
    function match($requestPath);

    /**
     * Renders the route destination using an IRequest instance
     * @param IRequest $Request the request to render
     * @return void
     */
    function render(IRequest $Request);

    function getPrefix();
    function getDestination();

    /**
     * Get a list of arguments that the constructor calls to instantiate this instance
     * @return Array
     */
    function getExportArgs();

    /**
     * Renders the route destination
     * @return IHandler
     * @throws InvalidHandlerException if the destination handler was invalid
     */
    function getHandler();

}