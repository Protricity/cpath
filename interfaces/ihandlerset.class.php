<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Interfaces;

class HandlerSetException extends \Exception {}

interface IHandlerSet extends IHandler, IRoutable, \ArrayAccess, \IteratorAggregate {
    /**
     * Adds an IHandler to the set by route
     * @param String $route the route to the sub api i.e. POST (any POST), GET search (relative), GET /site/users/search (absolute)
     * @param IHandler $Handler the IHandler instance to add to the set
     * @param bool $replace if true, replace any existing handlers
     * @throws HandlerSetException
     */
    function add($route, IHandler $Handler, $replace=false);

    /**
     * Returns an IHandler instance by route
     * @param $route String the route associated with this handler
     * @return IHandler|NULL
     */
    function get($route);

    /**
     * Returns the source class that created this instance
     * @return IHandlerAggregate the source class name
     */
    function getSource();

    /**
     * Returns an array of all routes for this class
     * @param IRouteBuilder $Builder the IRouteBuilder instance
     * @return IRoute[]
     * @throws \CPath\BuildException when a route is not in a valid format
     */
    function getAllRoutes(IRouteBuilder $Builder);
}