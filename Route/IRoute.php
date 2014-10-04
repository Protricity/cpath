<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 3/24/14
 * Time: 11:47 AM
 */
namespace CPath\Route;

use CPath\Request\IRequest;

interface IRoute {

    /**
     * Route the request to this class object and return the object
     * @param IRequest $Request the IRequest instance for this render
     * @param Object|null $Previous a previous response object that was passed to this handler or null
     * @param null|mixed $_arg [varargs] passed by route map
     * @return void|bool|Object returns a response object
     * If nothing is returned (or bool[true]), it is assumed that rendering has occurred and the request ends
     * If false is returned, this static handler will be called again if another handler returns an object
     * If an object is returned, it is passed along to the next handler
     */
    static function routeRequestStatic(IRequest $Request, $Previous=null, $_arg=null);
}