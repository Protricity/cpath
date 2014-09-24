<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/11/14
 * Time: 11:37 AM
 */
namespace CPath\Backend;
use CPath\Build\BuildRequestWrapper;
use CPath\Request\CLI\CLIRequest;
use CPath\Request\IRequest;
use CPath\Request\Request;
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
     * @return bool returns true if the route was rendered, false if no route was matched
     */
    static function route(IRequest $Request=null) {
        if(!$Request)
            $Request = Request::create();

        $Renderer = new RouteRenderer($Request);
        $Routes = new CPathBackendRoutes();

        return
            $Routes->mapRoutes($Renderer);
    }

    /**
     * Maps all routes to the route map. Returns true if the route prefix was matched
     * @param IRouteMap $Map
     * @return bool if true the route prefix was matched, otherwise false
     * @build routes --disable 0
     * Note: Set --disable 1 or remove doc tag to stop code auto-generation on build for this method
     */
    function mapRoutes(IRouteMap $Map) {		return
			// @group _last
			$Map->route('ANY /', '404') ||
			// @group CPath\Backend\TestRequestHandler
			$Map->route('CLI /cpath/test', 'CPath\\Backend\\TestRequestHandler') ||
			// @group CPath\Backend\BuildRequestHandler
			$Map->route('CLI /cpath/build', 'CPath\\Backend\\BuildRequestHandler') ||
			$Map->route('CLI /cpath/test', 'CPath\\Backend\\TestRequestHandler') ||
			// @group CPath\Backend\BackendIndexHandler
			$Map->route('ANY /cpath/', 'CPath\\Backend\\BackendIndexHandler');}
}