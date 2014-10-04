<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 10/3/14
 * Time: 11:47 PM
 */
namespace CPath\Handlers;

use CPath\Build\IBuildable;
use CPath\Build\IBuildRequest;
use CPath\Render\HTML\HTMLMimeType;
use CPath\Render\HTML\HTMLResponseBody;
use CPath\Render\HTML\IRenderHTML;
use CPath\Request\IRequest;
use CPath\Route\DefaultMap;
use CPath\Route\IRoute;
use CPath\Route\RouteBuilder;

class RenderHandler implements IRoute, IBuildable
{

	// Static

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
		if ($Previous instanceof IRenderHTML) {
			$MimeType = $Request->getMimeType();
			if (!$MimeType instanceof HTMLMimeType)
				return false;

			$Container = $MimeType->getRenderContainer() ? : new HTMLResponseBody();
			$Container->renderHTMLContent($Request, $Previous);
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
		$RouteBuilder = new RouteBuilder($Request, new DefaultMap(), '_render');
		$RouteBuilder->writeRoute('ANY *', __CLASS__);
	}
}