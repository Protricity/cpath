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
     * @param String|Array|null $methods the allowed methods
     * @param String|null $path the route path or null for default
     * @param IHandler|null $Handler the handler class instance
     * @return array
     */
    function getHandlerDefaultRoutes($methods='GET,POST,CLI', $path=NULL, IHandler $Handler=NULL);

    /**
     * Gets the default public route path for this handler
     * @param String|IHandler|NULL $Handler The class instance, class name, or NULL for the current class
     * @return string The public route path
     */
    function getHandlerDefaultPath($Handler=NULL);
}