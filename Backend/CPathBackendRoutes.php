<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/11/14
 * Time: 11:37 AM
 */
namespace CPath\Backend;
use CPath\Route\IRouteMap;
use CPath\Route\IRoutable;

/**
 * CPath Backend
 * Class Routes
 * @package CPath
 */
class CPathBackendRoutes implements IRoutable
{

    /**
     * Maps all routes to the route map. Returns true if the route prefix was matched
     * @param IRouteMap $Map
     * @return bool if true the route prefix was matched, otherwise false
     * @build routes --disable 0
     * Note: Set --disable 1 or remove doc tag to stop code auto-generation on build for this method
     */
    function mapRoutes(IRouteMap $Map) {
        return
            // @group /CPath
            $Map->route('CLI /build', BuildRequestHandler::cls()) ||
            $Map->route('GET /CPath/', BackendIndexHandler::cls()) ||
            $Map->route('GET /', '404');
    }
}

