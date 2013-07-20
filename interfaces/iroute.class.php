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
     * Try's a route against a request path
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

    function getRoute();
    function getDestination();

    function getCurrentArg();
    function hasNextArg();
    function getNextArg();
    function addToRoute($path);

    /**
     * Returns the request parameters.
     * If none are set, return the web request parameters ie $_GET, $_POST
     * @return Array the request parameters
     */
    function getRequest();

    /**
     * Set the request parameters
     * @param array $request the request parameters
     * @return void
     */
    function setRequest(Array $request);

}