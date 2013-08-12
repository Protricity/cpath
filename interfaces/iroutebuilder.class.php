<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Interfaces;


interface IRouteBuilder extends IBuilder {

    /**
     * Get all default routes for this Handler
     * @param String|IHandler $Handler The class instance or class name
     * @param String|Array|null $methods the allowed methods
     * @param String|null $path the route path or null for default
     * @return array
     */
    function getHandlerDefaultRoutes($Handler, $methods='GET,POST,CLI', $path=NULL);

    /**
     * Gets the default public route path for this handler
     * @param String|IHandler $Handler The class instance or class name
     * @return string The public route path
     */
    function getHandlerDefaultPath($Handler);
}