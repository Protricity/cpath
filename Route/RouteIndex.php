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

	public function __construct(IRouteMap $Routes, $routePrefix = 'GET /') {
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
		$matchPrefix = $this->mRoutePrefix;
		$this->mRoutes->mapRoutes(
			new RouteCallback(
				function ($prefix, $target) use ($Map, &$args, $matchPrefix) {
					list($matchMethod, $matchPath) = explode(' ', $matchPrefix, 2);
					list($routeMethod, $routePath) = explode(' ', $prefix, 2);
					if ($routeMethod !== 'ANY' && $matchMethod !== 'ANY' && $routeMethod !== $matchMethod)
						return false;

					$routePath = str_replace('\\', '/', $routePath);
					if (strpos($routePath, $matchPath) !== 0)
						return false;

					$Route = new RouteLink($prefix, $target);
					$Map->mapNext($Route);
					return false;
				}
			)
		);
	}

	/**
	 * Map data to the key map
	 * @param IKeyMapper $Map the map inst to add data to
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