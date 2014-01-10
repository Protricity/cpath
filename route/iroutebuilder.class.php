<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Route;


use CPath\Exceptions\BuildException;
use CPath\Interfaces\IBuilder;

interface IRouteBuilder extends IBuilder {
    /**
     * Add an IRoute to the Route Builder
     * @param IRoute $Route the route path or null for default
     * @param bool $replace
     * @return void
     * @throws BuildException
     */
    function addRoute(IRoute $Route, $replace=false);

    /**
     * @return IRoute[] associative array of routes and paths
     */
    function getRoutes();
}