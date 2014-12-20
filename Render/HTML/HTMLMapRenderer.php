<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 10/4/14
 * Time: 11:13 PM
 */
namespace CPath\Render\HTML;

use CPath\Build\IBuildable;
use CPath\Build\IBuildRequest;
use CPath\Data\Map\IKeyMap;
use CPath\Data\Map\ISequenceMap;
use CPath\Render\Helpers\RenderIndents as RI;
use CPath\Render\Map\AbstractMapRenderer;
use CPath\Request\IRequest;
use CPath\Route\CPathMap;
use CPath\Route\RouteBuilder;

class HTMLMapRenderer extends AbstractMapRenderer implements IBuildable
{
	public function __construct($Map) {
		parent::__construct($Map);
	}

	protected function renderKeyValue($key, $value) {
		echo RI::ni(), "<dt>", $key, "</dt>";
		echo RI::ni(), "<dd>";
		if($value instanceof IRenderHTML) {
			RI::ai(1);
			$value->renderHTML($this->getRequest());
			RI::ai(-1);
			$ret = true;
			echo RI::ni();

		} else {
			$ret = parent::renderKeyValue($key, $value);
		}
		echo "</dd>";
		return $ret;
	}

	protected function renderValue($value) {
		echo RI::ni(), "<li>";
		RI::ai(1);
		if($value instanceof IRenderHTML) {
			$value->renderHTML($this->getRequest());
			$ret = true;

		} else {
			$ret = parent::renderValue($value);
		}
		RI::ai(-1);
		echo RI::ni(), "</li>";
		return $ret;
	}


	protected function renderStart($isArray) {
		if($isArray === true) {
			echo RI::ni(), "<ul>";
			RI::ai(1);

		} else if($isArray === false) {
			echo RI::ni(), "<dl>";
			RI::ai(1);
		}
	}

	protected function renderEnd($isArray) {
		if($isArray === true) {
			RI::ai(-1);
			echo RI::ni(), "</ul>";

		} else if($isArray === false) {
			RI::ai(-1);
			echo RI::ni(), "</dl>";
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
	static function routeRequestStatic(IRequest $Request, Array &$Previous = array(), $_arg = null) {
		$Object = reset($Previous);
		if ($Request->getMimeType() instanceof HTMLMimeType)
			if ($Object instanceof IKeyMap || $Object instanceof ISequenceMap)
				return new static($Object);

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
		$RouteBuilder = new RouteBuilder($Request, new CPathMap(), '_map_html');
		$RouteBuilder->writeRoute('ANY *', __CLASS__);
	}
}