<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 10/3/14
 * Time: 11:45 PM
 */
namespace CPath\Handlers;

use CPath\Build\IBuildable;
use CPath\Build\IBuildRequest;
use CPath\Data\Map\IKeyMap;
use CPath\Data\Map\ISequenceMap;
use CPath\Render\HTML\HTMLMapRenderer;
use CPath\Request\IRequest;
use CPath\Route\DefaultMap;
use CPath\Route\IRoute;
use CPath\Route\RouteBuilder;

class MappableHandler implements IRoute, IBuildable
{

	/**
	 * Route the request to this class object and return the object
	 * @param IRequest $Request the IRequest instance for this render
	 * @param Object|null $Mappable a previous response object that was passed to this handler or null
	 * @param null|mixed $_arg [varargs] passed by route map
	 * @return void|bool|Object returns a response object
	 * If nothing is returned (or bool[true]), it is assumed that rendering has occurred and the request ends
	 * If false is returned, this static handler will be called again if another handler returns an object
	 * If an object is returned, it is passed along to the next handler
	 */
	static function routeRequestStatic(IRequest $Request, $Mappable = null, $_arg = null) {
		if ($Mappable instanceof IKeyMap) {
			$Renderer = new HTMLMapRenderer($Request);
			$Mappable->mapKeys($Renderer);

			return true;

		} elseif ($Mappable instanceof ISequenceMap) {
			$Renderer = new HTMLMapRenderer($Request);
			$Mappable->mapSequence($Renderer);

			return true;

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
		$RouteBuilder = new RouteBuilder($Request, new DefaultMap(), '_mappable');
		$RouteBuilder->writeRoute('ANY *', __CLASS__);
	}
}