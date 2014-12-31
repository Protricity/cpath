<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 12/12/2014
 * Time: 1:33 PM
 */
namespace CPath\Render\Text;

use CPath\Build\IBuildable;
use CPath\Build\IBuildRequest;
use CPath\Data\Map\IKeyMap;
use CPath\Data\Map\ISequenceMap;
use CPath\Render\Helpers\RenderIndents as RI;
use CPath\Render\Map\AbstractMapRenderer;
use CPath\Request\IRequest;
use CPath\Route\CPathMap;
use CPath\Route\RouteBuilder;

class TextMapRenderer extends AbstractMapRenderer implements IBuildable
{
	private static $mStarted = false;

	public function __construct(IRequest $Request, $Map) {
		parent::__construct($Request, $Map);
	}

	protected function renderKeyValue($key, $value) {
		if (self::$mStarted)
			echo RI::ni();
		self::$mStarted = true;
		echo $key, ": ";
		$ret = parent::renderKeyValue($key, $value);
		return $ret;
	}

	protected function renderValue($value) {
		if (self::$mStarted)
			echo RI::ni();
		self::$mStarted = true;
		$ret = parent::renderValue($value);
		return $ret;
	}


	protected function renderStart($isArray) {
	}

	protected function renderEnd($isArray) {
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
	static function routeRequestStatic(IRequest $Request, Array &$Previous = array(), $_arg = null) {
		$Object = reset($Previous);
		if ($Request->getMimeType() instanceof TextMimeType)
			if ($Object instanceof IKeyMap || $Object instanceof ISequenceMap)
				return new static($Request, $Object);

		return false;
	}

	/**
	 * Handle this request and render any content
	 * @param IBuildRequest $Request the build request inst for this build session
	 * @return void
	 * @build --disable 0
	 * Note: Use doctag 'build' with '--disable 1' to have this IBuildable class skipped during a build
	 */
	static function handleBuildStatic(IBuildRequest $Request) {
		$RouteBuilder = new RouteBuilder($Request, new CPathMap(), '_map_text');
		$RouteBuilder->writeRoute('ANY *', __CLASS__);
	}
}