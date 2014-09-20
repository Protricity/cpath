<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/11/14
 * Time: 11:37 AM
 */
namespace CPath\Backend;
use CPath\Request\CLI\CLIRequest;
use CPath\Request\IRequest;
use CPath\Request\Web\WebRequest;
use CPath\Route\IRouteMap;
use CPath\Route\IRoutable;
use CPath\Route\RouteRenderer;

/**
 * CPath Backend
 * Class Routes
 * @package CPath
 */
class CPathBackendRoutes implements IRoutable
{


    /**
     * Handle this request and render any content
     * @param IRequest $Request the IRequest instance for this render
     * @return String|void always returns void
     */
    static function route(IRequest $Request=null) {
        if($Request);
        elseif(php_sapi_name() === 'cli')
            $Request = new CLIRequest();
        else
            $Request = new WebRequest();

        $Renderer = new RouteRenderer($Request);
        $Routes = new CPathBackendRoutes();
        $found =
            $Routes->mapRoutes($Renderer);
    }

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
            $Map->route('ANY /cpath/build', BuildRequestHandler::cls()) ||
            $Map->route('GET /cpath/', BackendIndexHandler::cls()) ||
            $Map->route('GET /', '404');
    }
}

