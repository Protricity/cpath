<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Interfaces;


use CPath\DestinationNotFoundException;
use CPath\InvalidHandlerException;

interface IRoute {

    /**
     * Try's a route against a request path.
     * If the match is successful, this method processes the requestPath as input
     * @param string|null $requestPath the request path to match
     * @return bool whether or not the path matched
     * @throws DestinationNotFoundException if the destination handler was not found
     * @throws InvalidHandlerException if the destination handler was invalid
     */
    function match($requestPath);

    /**
     * Renders the route destination
     * @return void
     */
    function render();

    function getPrefix();
    function getDestination();

    function getNextArg();

    /**
     * Merge an associative array into the existing request array
     * @param $request Array associative array to merge
     */
    function addRequest(Array $request);

    /**
     * Returns the request parameters.
     * If none are set, return the web request parameters ie $_GET, $_POST
     * @return Array the request parameters
     */
    function getRequest();

    /**
     * Append a path element to the route
     * @param $path String the element to append
     */
    function addToRoute($path);
}