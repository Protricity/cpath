<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Framework\Route\Routable;

use CPath\Framework\Route\Map\IRouteMap;
use CPath\Route\IRoute;

interface IRoutable {

    const METHODS = 'GET,POST,PUT,PATCH,DELETE,CLI';

    /**
     * Returns the route for this IRender
     * @param IRouteMap $Map
     */
    function mapRoutes(IRouteMap $Map);
}