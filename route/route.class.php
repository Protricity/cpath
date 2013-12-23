<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Route;
use CPath\Interfaces\IHandler;

/**
 * Class Route - a route entry
 * @package CPath
 */
class Route extends AbstractRoute {



    // Static

    /**
     * Creates a new Route for an IHandler
     * @param IHandler $Handler The class instance or class name
     * @param String $method the route prefix method (GET, POST...) for this IHandler
     * @param String $path a custom path for this IHandler
     * @return Route
     */
    static final function fromHandler(IHandler $Handler, $method='ANY', $path=NULL) {
        $prefix = RoutableSet::getPrefixFromHandler($Handler, $method, $path);
        return new static($prefix, get_class($Handler));
    }
}