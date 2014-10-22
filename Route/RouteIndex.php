<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 10/20/14
 * Time: 1:20 AM
 */
namespace CPath\Route;

use CPath\Data\Map\IKeyMap;
use CPath\Data\Map\IKeyMapper;
use CPath\Data\Map\ISequenceMap;
use CPath\Data\Map\ISequenceMapper;

class RouteIndex implements ISequenceMap, IKeyMap
{

	const STR_PATH   = 'path';
	const STR_METHOD = 'method';
	const STR_ROUTES = 'routes';

	private $mRoutes;
	private $mRoutePrefix;
	private $mArgs = null;

	public function __construct(IRouteMap $Routes, $routePrefix = null) {
		$this->mRoutes      = $Routes;
		$this->mRoutePrefix = $routePrefix;
	}

	/**
	 * Map sequential data to the map
	 * @param ISequenceMapper $Map
	 * @internal param \CPath\Request\IRequest $Request
	 * @return void
	 */
	function mapSequence(ISequenceMapper $Map) {
		$args = & $this->mArgs;
		$this->mRoutes->mapRoutes(
			new RouteCallback(
				function ($prefix, $target) use ($Map, &$args) {
					$Map->mapNext(new MappedRoute(func_get_args()));
				}
			)
		);
	}

	/**
	 * Map data to the key map
	 * @param IKeyMapper $Map the map instance to add data to
	 * @internal param \CPath\Request\IRequest $Request
	 * @internal param \CPath\Request\IRequest $Request
	 * @return void
	 */
	function mapKeys(IKeyMapper $Map) {
//        $Map->map(IResponse::STR_CODE, $this->getCode());
//        $Map->map(IResponse::STR_MESSAGE, $this->getMessage());
//		$Map->map(self::STR_PATH, new URLValue($Request->getPath(), $Request->getPath()));
//		$Map->map(self::STR_METHOD, $Request->getMethodName());
		$Map->map(self::STR_ROUTES, $this);
	}

}