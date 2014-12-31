<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Route;

define('IRouteMap', __NAMESPACE__ . '\\IRouteMap');
interface IRouteMap {
	const FLAG_SESSION_ONLY = 0x10;
	const FLAG_NO_SESSION   = 0x20;

    /**
     * Maps all routes to the route map. Returns true if the route prefix was matched
     * @param IRouteMapper $Map
     * @return bool true if the route mapper should stop mapping, otherwise false to continue
     * @build routes --disable 0
     * Note: Set --disable 1 or remove doc tag to stop code auto-generation on build for this method
     */
    function mapRoutes(IRouteMapper $Map);
}