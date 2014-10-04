<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/5/14
 * Time: 4:41 PM
 */
namespace CPath\Handlers;

use CPath\Build\IBuildRequest;
use CPath\Data\Map\IKeyMap;
use CPath\Data\Map\IKeyMapper;
use CPath\Data\Map\ISequenceMap;
use CPath\Data\Map\ISequenceMapper;
use CPath\Handlers\Common\MappedRoute;
use CPath\Request\IRequest;
use CPath\Route\DefaultMap;
use CPath\Route\IRoute;
use CPath\Route\IRouteMap;
use CPath\Route\RouteBuilder;
use CPath\Route\RouteCallback;

class RouteIndexHandler implements ISequenceMap, IKeyMap, IRoute
{
    const STR_ROUTES = 'routes';

    private $mRoutes;
    private $mRoutePrefix;
    public function __construct(IRouteMap $Routes, $routePrefix=null) {
        $this->mRoutes = $Routes;
        $this->mRoutePrefix = $routePrefix;
    }

    /**
     * Map sequential data to the map
     * @param ISequenceMapper $Map
     * @return void
     */
    function mapSequence(ISequenceMapper $Map) {
        $this->mRoutes->mapRoutes(
            new RouteCallback(
                function($prefix, $target) use ($Map) {
                    $Map->mapNext(new MappedRoute(func_get_args()));
                }
            )
        );
    }

    /**
     * Map data to the key map
     * @param IKeyMapper $Map the map instance to add data to
     * @internal param \CPath\Request\IRequest $Request
     * @return void
     */
    function mapKeys(IKeyMapper $Map) {
//        $Map->map(IResponse::STR_CODE, $this->getCode());
//        $Map->map(IResponse::STR_MESSAGE, $this->getMessage());
        $Map->map(self::STR_ROUTES, $this);
    }


    // Static

    /**
     * Handle this request and render any content
     * @param IBuildRequest $Request the build request instance for this build session
     * @return String|void always returns void
     * @build --disable 0
     * Note: Use doctag 'build' with '--disable 1' to have this IBuildable class skipped during a build
     */
    static function handleStaticBuild(IBuildRequest $Request) {
        $RouteBuilder = new RouteBuilder($Request, new DefaultMap(), '_404');
        $RouteBuilder->writeRoute('ANY *', __CLASS__);
    }

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
	static function routeRequestStatic(IRequest $Request, $Previous = null, $_arg = null) {
		var_dump($Previous);
		if($Previous instanceof IRouteMap) {
			$Map = $Previous;
			$Previous = null;
		} else {
			$Map = new DefaultMap();
		}
		$routePrefix = 'GET ' . $Request->getPath();
		$Route = new RouteIndexHandler($Map, $routePrefix);
		return $Route;
		//ExecutableRouteHandler::routeRequestStatic($Request, $Handler);
		//return $Handler;
//        $Response = $Handler->execute($Request); //new HTTPRequestException("Route not found: " . $Request->getPath(), IResponse::HTTP_NOT_FOUND);
//        $Handler = new ResponseRenderer($Response);
//        $Handler->render($Request);
	}
}

