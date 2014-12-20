<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 12/1/14
 * Time: 5:20 PM
 */
namespace CPath\Route\HTML;

use CPath\Render\HTML\Attribute\IAttributes;
use CPath\Render\HTML\Element\HTMLAnchor;
use CPath\Render\HTML\IRenderHTML;
use CPath\Request\IRequest;
use CPath\Route\IRouteMap;
use CPath\Route\RouteCallback;

class HTMLRouteNavigator implements IRenderHTML
{
	private $mRoute;
	private $mSubPaths;
	private $mMatch;
	private $mPathNames = array();

	public function __construct(IRouteMap $Route, $includeSubPaths = false, $matchPrefix = 'GET /') { // 'ANY /') {
		$this->mRoute = $Route;
		$this->mSubPaths = $includeSubPaths;
		$this->mMatch = $matchPrefix;
	}


	/**
	 * Render request as html
	 * @param IRequest $Request the IRequest inst for this render which contains the request and remaining args
	 * @param IAttributes $Attr
	 * @param IRenderHTML $Parent
	 * @return String|void always returns void
	 */
	function renderHTML(IRequest $Request, IAttributes $Attr = null, IRenderHTML $Parent = null) {
		$Route = $this->mRoute;

		$curPath = dirname($Request->getPath());

		$match = $this->mMatch;
		if(!$match)
			$match = $Request->getMethodName() . ' ' . $curPath;
		$match = str_replace('\\', '/', $match);

		$this->renderRoute($Request, $Request->getMethodName() . ' ' . $curPath, '.');
		if(dirname($curPath))
			$this->renderRoute($Request, $Request->getMethodName() . ' ' . dirname($curPath), '..');

		$THIS = $this;
		$Route->mapRoutes(
			new RouteCallback(
				function($prefix, $target, $_arg = null) use ($Request, $THIS, $match) {
					list($matchMethod, $matchPath) = explode(' ', $match, 2);
					list($routeMethod, $routePath) = explode(' ', $prefix, 2);
					if ($routeMethod !== 'ANY' && $matchMethod !== 'ANY' && $routeMethod !== $matchMethod)
						return false;

					$routePath = str_replace('\\', '/', $routePath);
					if (strpos($routePath, $matchPath) !== 0)
						return false;

					return $THIS->renderRoute($Request, $routeMethod . ' ' . $routePath);
				}
			)
		);
	}


	function renderRoute(IRequest $Request, $prefix, $title=null) {
		list($routeMethod, $routePath) = explode(' ', $prefix, 2);

		$routePath = str_replace('\\', '/', $routePath);

		if(!$this->mSubPaths) {
			if(($c = strpos($routePath, '/', 1)) > 0)
				$routePath = substr($routePath, 0, $c + 1);
			if(in_array($routePath, $this->mPathNames))
				return false;
			$this->mPathNames[] = $routePath;
		}
		if(!$title)
			$title = ltrim($routePath, '/');

		$Anchor = new HTMLAnchor($routePath, $title);
		$Anchor->renderHTML($Request);

		return false;
	}
}