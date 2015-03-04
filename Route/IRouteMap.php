<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Route;

use CPath\Request\IRequest;

define('IRouteMap', __NAMESPACE__ . '\\IRouteMap');
interface IRouteMap {

    /**
     * Maps all routes to the route map. Returns true if the route prefix was matched
     * @param IRequest $Request
     * @param IRouteMapper $Mapper
     * @return bool true if the route mapper should stop mapping, otherwise false to continue
     * @build routes --disable 0
     * Note: Set --disable 1 or remove doc tag to stop code auto-generation on build for this method
     */
    function mapRoutes(IRequest $Request, IRouteMapper $Mapper);
}