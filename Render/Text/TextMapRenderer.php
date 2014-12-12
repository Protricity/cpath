<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 3/23/14
 * Time: 9:12 AM
 */
namespace CPath\Render\Text;

use CPath\Build\IBuildable;
use CPath\Build\IBuildRequest;
use CPath\Data\Map\IKeyMap;
use CPath\Data\Map\ISequenceMap;
use CPath\Render\HTML\Attribute\IAttributes;
use CPath\Request\IRequest;
use CPath\Route\CPathMap;
use CPath\Route\IRoutable;
use CPath\Route\RouteBuilder;

class TextMapRenderer implements IRenderText, IRoutable, IBuildable
{
	private $mMap;

	/**
	 * @param IKeyMap|ISequenceMap $Map
	 */
	public function __construct($Map) {
		$this->mMap = $Map;
	}

	/**
	 * Render request as text
	 * @param IRequest $Request the IRequest inst for this render which contains the request and remaining args
	 * @param IAttributes $Attr
	 * @param IRenderText $Parent
	 * @return String|void always returns void
	 */
	function renderText(IRequest $Request, IAttributes $Attr = null, IRenderText $Parent = null) {
		$Mappable = $this->mMap;
		$Renderer = new TextMapper($Request);
		if ($Mappable instanceof IKeyMap) {
			$Mappable->mapKeys($Renderer);

		} elseif ($Mappable instanceof ISequenceMap) {
			$Mappable->mapSequence($Renderer);
		}
	}

	// Static

	/**
	 * Route the request to this class object and return the object
	 * @param IRequest $Request the IRequest inst for this render
	 * @param array|null $Previous all previous response object that were passed from a handler, if any
	 * @param null|mixed $_arg [varargs] passed by route map
	 * @return void|bool|Object returns a response object
	 * If nothing is returned (or bool[true]), it is assumed that rendering has occurred and the request ends
	 * If false is returned, this static handler will be called again if another handler returns an object
	 * If an object is returned, it is passed along to the next handler
	 */
	static function routeRequestStatic(IRequest $Request, Array $Previous = array(), $_arg = null) {
		$Object = reset($Previous);
		if ($Request->getMimeType() instanceof TextMimeType) {
			if ($Object instanceof IKeyMap)
				return new TextMapRenderer($Object);
			if ($Object instanceof ISequenceMap)
				return new TextMapRenderer($Object);
		}

		return false;
	}

	/**
	 * Handle this request and render any content
	 * @param IBuildRequest $Request the build request inst for this build session
	 * @return void
	 * @build --disable 0
	 * Note: Use doctag 'build' with '--disable 1' to have this IBuildable class skipped during a build
	 */
	static function handleStaticBuild(IBuildRequest $Request) {
		$RouteBuilder = new RouteBuilder($Request, new CPathMap(), '__map_text');
		$RouteBuilder->writeRoute('ANY *', __CLASS__);
	}
}

