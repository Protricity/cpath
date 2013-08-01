<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Interfaces;


interface IRouteBuilder extends IBuilder {

    const METHODS = 'GET|POST|PUT|DELETE|CLI';
    /**
     * Returns a list of allowed route methods associated with this class
     * @param \ReflectionClass $Class
     * @return array a list of methods
     * @throws \CPath\BuildException if no class was specified and no default class exists
     */
    function getHandlerMethods(\ReflectionClass $Class=NULL);

    /**
     * Gets the default public route path for this handler
     * @param \ReflectionClass $Class|NULL The class instance or NULL for the current class
     * @return string The public route path
     */
    function getHandlerDefaultPath(\ReflectionClass $Class=NULL);
}