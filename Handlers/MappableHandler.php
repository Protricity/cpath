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
use CPath\Framework\Render\Header\IHeaderWriter;
use CPath\Framework\Render\Header\IHTMLSupportHeaders;
use CPath\Render\HTML\Attribute\IAttributes;
use CPath\Render\HTML\HTMLKeyMapRenderer;
use CPath\Render\HTML\HTMLSequenceMapRenderer;
use CPath\Render\HTML\IRenderHTML;
use CPath\Render\JSON\IRenderJSON;
use CPath\Render\JSON\JSONKeyMapRenderer;
use CPath\Render\JSON\JSONSequenceMapRenderer;
use CPath\Render\Text\IRenderText;
use CPath\Render\Text\TextKeyMapRenderer;
use CPath\Render\Text\TextSequenceMapRenderer;
use CPath\Render\XML\IRenderXML;
use CPath\Render\XML\XMLKeyMapRenderer;
use CPath\Render\XML\XMLSequenceMapRenderer;
use CPath\Request\IRequest;
use CPath\Route\DefaultMap;
use CPath\Route\IRoute;
use CPath\Route\RouteBuilder;

class MappableHandler implements IRoute, IBuildable, IRenderHTML, IRenderXML, IRenderJSON, IRenderText, IHTMLSupportHeaders
{
	private $mMappable;
	public function __construct($Mappable) {
		$this->mMappable = $Mappable;
	}

	/**
	 * Write all support headers used by this IView instance
	 * @param IRequest $Request
	 * @param IHeaderWriter $Head the writer instance to use
	 * @return String|void always returns void
	 */
	function writeHeaders(IRequest $Request, IHeaderWriter $Head) {
		$Mappable = $this->mMappable;
		if ($Mappable instanceof IKeyMap) {
			$Renderer = new HTMLKeyMapRenderer($Request);
			$Renderer->writeHeaders($Request, $Head);

		} elseif ($Mappable instanceof ISequenceMap) {
			$Renderer = new HTMLSequenceMapRenderer($Request);
			$Renderer->writeHeaders($Request, $Head);
		}
	}

	/**
	 * Render request as html
	 * @param IRequest $Request the IRequest instance for this render which contains the request and remaining args
	 * @param IAttributes $Attr
	 * @return String|void always returns void
	 */
	function renderHTML(IRequest $Request, IAttributes $Attr = null) {
		$Mappable = $this->mMappable;
		if ($Mappable instanceof IKeyMap) {
			$Renderer = new HTMLKeyMapRenderer($Request);
			$Mappable->mapKeys($Request, $Renderer);

		} elseif ($Mappable instanceof ISequenceMap) {
			$Renderer = new HTMLSequenceMapRenderer($Request);
			$Mappable->mapSequence($Request, $Renderer);
		}
	}


	/**
	 * Render request as JSON
	 * @param \CPath\Request\IRequest $Request the IRequest instance for this render which contains the request and remaining args
	 * @return String|void always returns void
	 */
	function renderJSON(IRequest $Request) {
		$Mappable = $this->mMappable;
		if ($Mappable instanceof IKeyMap) {
			$Renderer = new JSONKeyMapRenderer($Request);
			$Mappable->mapKeys($Request, $Renderer);

		} elseif ($Mappable instanceof ISequenceMap) {
			$Renderer = new JSONSequenceMapRenderer($Request);
			$Mappable->mapSequence($Request, $Renderer);
		}
	}

	/**
	 * Render request as plain text
	 * @param IRequest $Request the IRequest instance for this render which contains the request and remaining args
	 * @return String|void always returns void
	 */
	function renderText(IRequest $Request) {
		$Mappable = $this->mMappable;
		if ($Mappable instanceof IKeyMap) {
			$Renderer = new TextKeyMapRenderer($Request);
			$Mappable->mapKeys($Request, $Renderer);

		} elseif ($Mappable instanceof ISequenceMap) {
			$Renderer = new TextSequenceMapRenderer($Request);
			$Mappable->mapSequence($Request, $Renderer);
		}
	}

	/**
	 * Render request as xml
	 * @param \CPath\Request\IRequest $Request the IRequest instance for this render which contains the request and remaining args
	 * @param string $rootElementName Optional name of the root element
	 * @param bool $declaration if true, the <!xml...> declaration will be rendered
	 * @return String|void always returns void
	 */
	function renderXML(IRequest $Request, $rootElementName = 'root', $declaration = false) {
		$Mappable = $this->mMappable;
		if ($Mappable instanceof IKeyMap) {
			$Renderer = new XMLKeyMapRenderer($Request, $rootElementName, $declaration);
			$Mappable->mapKeys($Request, $Renderer);

		}

		if ($Mappable instanceof ISequenceMap) {
			$Renderer = new XMLSequenceMapRenderer($Request, $rootElementName, $declaration);
			$Mappable->mapSequence($Request, $Renderer);
		}
	}

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
			return new MappableHandler($Mappable);

		} elseif ($Mappable instanceof ISequenceMap) {
			return new MappableHandler($Mappable);

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