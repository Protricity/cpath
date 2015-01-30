<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/11/14
 * Time: 11:37 AM
 */
namespace CPath\Route;
use CPath\Request\IRequest;
use CPath\Request\Request;

/**
 * CPath Backend
 * Class Routes
 * @package CPath
 */
class CPathMap implements IRouteMap
{

    /**
     * Maps all routes to the route map. Returns true if the route prefix was matched
     * @param IRouteMapper $Map
     * @return bool if true the route prefix was matched, otherwise false
     * @build routes --disable 0
     * Note: Set --disable 1 or remove doc tag to stop code auto-generation on build for this method
     */
    function mapRoutes(IRouteMapper $Map) {
		return
			// @group CPath\Build\Handlers\BuildRequestHandler
			$Map->route('CLI /cpath/build', 'CPath\\Build\\Handlers\\BuildRequestHandler') ||

			// @group CPath\UnitTest\Handlers\TestRequestHandler
			$Map->route('CLI /cpath/test', 'CPath\\UnitTest\\Handlers\\TestRequestHandler') ||

			// @group __executable
			$Map->route('ANY *', 'CPath\\Request\\Executable\\ExecutableRenderer') ||

			// @group __map
			$Map->route('ANY *', 'CPath\\Render\\Map\\MapRenderer') ||

			// @group __response
			$Map->route('ANY *', 'CPath\\Response\\ResponseRenderer') ||

			// @group _default_template
			$Map->route('ANY *', 'CPath\\Render\\HTML\\Template\\DefaultCPathTemplate');
	}

    // Static

//	static function map(IRouteMapper $Map) {
//		$Inst = new CPathMap();
//		return $Inst->mapRoutes($Map);
//	}

    /**
     * Handle this request and render any content
     * @param IRequest $Request the IRequest inst for this render
     * @return bool returns true if the route was rendered, false if no route was matched
     */
    static function route(IRequest $Request=null) {
        if(!$Request)
            $Request = Request::create();

        $Renderer = new RouteRenderer($Request);
        return $Renderer->renderRoutes(new CPathMap);
    }
}