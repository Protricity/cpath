<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Route;
use CPath\Interfaces\IBuildable;
use CPath\Interfaces\IHandler;
use CPath\Interfaces\IRequest;

/**
 * Class Route - a route entry
 * @package CPath
 */
class Route extends AbstractRoute {

    /**
     * Match the destination to the route and return an instance of the destination object
     * Note: this method should throw an exception if the requested route (method + path) didn't match
     * @param IRequest $Request the request to render
     * @return IHandler
     */
    function routeRequestToHandler(IRequest $Request) {
        return $this->getHandler();
    }
}