<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/5/14
 * Time: 4:20 PM
 */
namespace CPath\Handlers;

use CPath\Build\IBuildable;
use CPath\Build\IBuildRequest;
use CPath\Request\IRequest;
use CPath\Response\Common\ExceptionResponse;
use CPath\Response\IResponse;
use CPath\Response\ResponseRenderer;
use CPath\Route\DefaultMap;
use CPath\Route\IRoute;
use CPath\Route\RouteBuilder;

class ExceptionHandler implements IRoute, IBuildable
{

    /**
     * Route the request to this class object and return the object
     * @param IRequest $Request the IRequest instance for this render
     * @param IRoute|null $Previous
     * @param null|mixed $_arg [varargs] passed by route map
     * @return void|bool|Object returns a response object
     * If nothing is returned (or bool[true]), it is assumed that rendering has occurred and the request ends
     * If false is returned, this static handler will be called again if another handler returns an object
     * If an object is returned, it is passed along to the next handler
     */
    static function routeRequestStatic(IRequest $Request, $Previous=null, $_arg=null) {
        if($Previous instanceof \Exception && !$Previous instanceof IResponse) {
            return new ExceptionResponse($Previous);
        }
        return false;
    }

    /**
     * Handle this request and render any content
     * @param IBuildRequest $Request the build request instance for this build session
     * @return String|void always returns void
     * @build --disable 0
     * Note: Use doctag 'build' with '--disable 1' to have this IBuildable class skipped during a build
     */
    static function handleStaticBuild(IBuildRequest $Request) {
        $RouteBuilder = new RouteBuilder($Request, new DefaultMap(), '_ex');
        $RouteBuilder->writeRoute('ANY *', __CLASS__);
    }
}