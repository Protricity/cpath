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
use CPath\Request\Session\ISessionRequest;
use CPath\Route\IRouteMap;
use CPath\Route\RouteCallback;

class HTMLRouteNavigator implements IRenderHTML
{
	private $mRoute;
	private $mSubPathLevel;
	private $mMatch;
	private $mPathNames = array();

	public function __construct(IRouteMap $Route, $subPathLevel = 1, $matchPrefix = 'GET /') { // 'ANY /') {
		$this->mRoute = $Route;
		$this->mSubPathLevel = $subPathLevel;
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
				function($prefix, $target, $flags = 0) use ($Request, $THIS, $match) {
					if(is_int($flags)) {
						if($flags & IRequest::MATCH_NO_SESSION) {
							if($Request instanceof ISessionRequest
								&& $Request->hasActiveSession())
								return false;
						}
						elseif($flags & IRequest::MATCH_SESSION_ONLY) {
							if(!$Request instanceof ISessionRequest
								|| !$Request->hasActiveSession())
								return false;
						}
					}

					$matchPath = $match;
					$matchMethod = 'GET';
					if(strpos($matchPath, ' ') !== false)
						list($matchMethod, $matchPath) = explode(' ', $matchPath, 2);
					list($routeMethod, $routePath) = explode(' ', $prefix, 2);
					if ($routeMethod !== 'ANY' && $matchMethod !== 'ANY' && $routeMethod !== $matchMethod)
						return false;

					$dirPath = dirname($matchPath);
					if($dirPath === '\\')
						$dirPath = '/';

					$routePath = str_replace('\\', '/', $routePath);
					if (strpos($routePath, $dirPath) !== 0)
						return false;

					return $THIS->renderRoute($Request, $routeMethod . ' ' . $routePath);
				}
			)
		);
	}


	function renderRoute(IRequest $Request, $prefix, $title=null) {
		list($routeMethod, $routePath) = explode(' ', $prefix, 2);

		$routePath = str_replace('\\', '/', $routePath);

		$routeArgs = explode('/', trim($routePath, '/'));

		$routePath = '/';
		for($i=0; $i<$this->mSubPathLevel; $i++)
			if(!empty($routeArgs[$i]) && $routeArgs[$i][0] !== ':')
				$routePath .= $routeArgs[$i] . '/';

		if(in_array($routePath, $this->mPathNames))
			return false;
		$this->mPathNames[] = $routePath;
		if(!$title)
			$title = rtrim($routePath, '/');

		$Anchor = new HTMLAnchor($routePath, $title);
		$Anchor->renderHTML($Request);

		return false;
	}
}