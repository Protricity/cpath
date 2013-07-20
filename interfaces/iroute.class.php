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
    function tryRoute($requestPath);


    function getRoute();
    function getDestination();

    function getCurrentArg();
    function hasNextArg();
    function getNextArg();
    function addToRoute($path);

}