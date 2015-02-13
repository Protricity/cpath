<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 12/12/2014
 * Time: 2:57 PM
 */
namespace CPath\Render\Map;

use CPath\Build\IBuildable;
use CPath\Build\IBuildRequest;
use CPath\Data\Map\ArrayKeyMap;
use CPath\Data\Map\ArraySequence;
use CPath\Data\Map\IKeyMap;
use CPath\Data\Map\ISequenceMap;
use CPath\Render\HTML\Attribute\IAttributes;
use CPath\Render\HTML\HTMLMapRenderer;
use CPath\Render\HTML\HTMLMimeType;
use CPath\Render\HTML\IRenderHTML;
use CPath\Render\IRenderAll;
use CPath\Render\JSON\JSONMapRenderer;
use CPath\Render\JSON\JSONMimeType;
use CPath\Render\Text\TextMapRenderer;
use CPath\Render\Text\TextMimeType;
use CPath\Render\XML\XMLMapRenderer;
use CPath\Render\XML\XMLMimeType;
use CPath\Request\IRequest;
use CPath\Route\CPathMap;
use CPath\Route\IRoutable;
use CPath\Route\RouteBuilder;

class MapRenderer implements IRenderAll, IBuildable, IRoutable
{
	const CustomHTMLMapRenderer = 'CPath\\Render\\Map\\CustomHTMLMapRenderer';
	const CustomJSONMapRenderer = 'CPath\\Render\\Map\\CustomJSONMapRenderer';
	const CustomXMLMapRenderer = 'CPath\\Render\\Map\\CustomXMLMapRenderer';
	const CustomTextMapRenderer = 'CPath\\Render\\Map\\CustomTextMapRenderer';

	private $mMap;

	/**
	 * @param IKeyMap|ISequenceMap $Map
	 * @internal param IRequest $Request
	 */
	public function __construct($Map) {
		if(is_array($Map)) {
			if(is_numeric(key($Map))) {
				$Map = new ArraySequence($Map);
			} else {
				$Map = new ArrayKeyMap($Map);
			}
		}
		$this->mMap = $Map;
	}

	/**
	 * @param IRequest $Request
	 * @return AbstractMapRenderer
	 */
	function getRenderer(IRequest $Request) {
		$Mime = $Request->getMimeType();
		if($Mime instanceof HTMLMimeType) {
			if(class_exists($c = self::CustomHTMLMapRenderer, false))
				return new $c($Request, $this->mMap);
			return new HTMLMapRenderer($Request, $this->mMap);

		} else if($Mime instanceof JSONMimeType) {
			if(class_exists($c = self::CustomJSONMapRenderer, false))
				return new $c($Request, $this->mMap);
			return new JSONMapRenderer($Request, $this->mMap);

		} else if($Mime instanceof XMLMimeType) {
			if(class_exists($c = self::CustomXMLMapRenderer, false))
				return new $c($Request, $this->mMap);
			return new XMLMapRenderer($Request, $this->mMap);

		} else if($Mime instanceof TextMimeType) {
			if(class_exists($c = self::CustomTextMapRenderer, false))
				return new $c($Request, $this->mMap);
			return new TextMapRenderer($Request, $this->mMap);
		}

		throw new \InvalidArgumentException("Invalid Mimetype: " . $Mime->getName());
	}

	/**
	 * Renders a response object or returns false
	 * @param IRequest $Request the IRequest inst for this render
	 * @param bool $sendHeaders if true, sends the response headers
	 * @return bool returns false if no rendering occurred
	 */
	function render(IRequest $Request, $sendHeaders = true) {
		$Renderer = $this->getRenderer($Request);
		$Renderer->render($Request, $sendHeaders);
	}

	/**
	 * Render request as html
	 * @param IRequest $Request the IRequest inst for this render which contains the request and remaining args
	 * @param IAttributes $Attr
	 * @param IRenderHTML $Parent
	 * @return String|void always returns void
	 */
	function renderHTML(IRequest $Request, IAttributes $Attr = null, IRenderHTML $Parent = null) {
		$this->render($Request);
	}

	/**
	 * Render request as JSON
	 * @param \CPath\Request\IRequest $Request the IRequest inst for this render which contains the request and remaining args
	 * @return String|void always returns void
	 */
	function renderJSON(IRequest $Request) {
		$this->render($Request);
	}

	/**
	 * Render request as plain text
	 * @param IRequest $Request the IRequest inst for this render which contains the request and remaining args
	 * @return String|void always returns void
	 */
	function renderText(IRequest $Request) {
		$this->render($Request);
	}

	/**
	 * Render request as xml
	 * @param \CPath\Request\IRequest $Request the IRequest inst for this render which contains the request and remaining args
	 * @param string $rootElementName Optional name of the root element
	 * @param bool $declaration if true, the <!xml...> declaration will be rendered
	 * @return String|void always returns void
	 */
	function renderXML(IRequest $Request, $rootElementName = 'root', $declaration = false) {
		$this->render($Request);
	}

	// Status

	/**
	 * Handle this request and render any content
	 * @param IBuildRequest $Request the build request inst for this build session
	 * @return void
	 * @build --disable 0
	 * Note: Use doctag 'build' with '--disable 1' to have this IBuildable class skipped during a build
	 */
	static function handleBuildStatic(IBuildRequest $Request) {
		$RouteBuilder = new RouteBuilder($Request, new CPathMap(), '__map');
		$RouteBuilder->writeRoute('ANY *', __CLASS__);
	}

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
		if($Object instanceof ISequenceMap || $Object instanceof IKeyMap) {
			$Renderer = new MapRenderer($Object);
			return $Renderer;
		}
		return false;
	}
}