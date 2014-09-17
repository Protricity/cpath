<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/11/14
 * Time: 5:32 PM
 */
namespace CPath\Backend;

use CPath\Build\IBuildable;
use CPath\Build\IBuildRequest;
use CPath\Request\IRequest;
use CPath\Request\IStaticRequestHandler;
use CPath\Route\RouteBuilder;

class BackendIndexHandler implements IStaticRequestHandler, IBuildable
{
    /**
     * Handle this request and render any content
     * @param IRequest $Request the IRequest instance for this render
     * @return String|void always returns void
     */
    function handleStaticRequest(IRequest $Request)
    {
        // TODO: Implement handleRequest() method.
    }

    // Static

    /**
     * Handle this request and render any content
     * @param IBuildRequest $Request the build request instance for this build session
     * @return String|void always returns void
     */
    static function handleStaticBuild(IBuildRequest $Request) {
        $Builder = new RouteBuilder($Request, new CPathBackendRoutes());
        $Builder->writeRoute('GET /CPath/', __CLASS__);
    }

    static function cls() {
        return __CLASS__;
    }
}