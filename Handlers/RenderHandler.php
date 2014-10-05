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
use CPath\Framework\Render\Header\IHeaderWriter;
use CPath\Framework\Render\Header\IHTMLSupportHeaders;
use CPath\Handlers\Response\ResponseUtil;
use CPath\Render\HTML\Attribute\IAttributes;
use CPath\Render\HTML\HTMLMimeType;
use CPath\Render\HTML\HTMLResponseBody;
use CPath\Render\HTML\IRenderHTML;
use CPath\Render\JSON\IRenderJSON;
use CPath\Render\JSON\JSONMimeType;
use CPath\Render\Text\IRenderText;
use CPath\Render\Text\TextMimeType;
use CPath\Render\XML\IRenderXML;
use CPath\Render\XML\XMLMimeType;
use CPath\Request\IRequest;
use CPath\Request\MimeType\UnknownMimeType;
use CPath\Response\IHeaderResponse;
use CPath\Response\IResponse;
use CPath\Route\DefaultMap;
use CPath\Route\IRoute;
use CPath\Route\RouteBuilder;

class RenderHandler implements IRoute, IBuildable, IRenderHTML, IRenderText, IRenderJSON, IRenderXML
{
	private $mRenderer;
	public function __construct($Renderer) {
		$this->mRenderer = $Renderer;
	}

	/**
	 * Write all support headers used by this IView instance
	 * @param IRequest $Request
	 * @param IHeaderWriter $Head the writer instance to use
	 * @return String|void always returns void
	 */
	function writeHeaders(IRequest $Request, IHeaderWriter $Head) {
//		$Renderer = new HTMLSequenceMapRenderer($Request);
//		$Renderer->writeHeaders($Request, $Head);
		$Mappable = $this->mRenderer;
		if($Mappable instanceof IHTMLSupportHeaders)
			$Mappable->writeHeaders($Request, $Head);
	}

	/**
	 * Renders a response object or returns false
	 * @param IRequest $Request the IRequest instance for this render
	 * @param bool $sendHeaders
	 * @internal param \CPath\Request\Executable\IExecutable|\CPath\Response\IResponse $Response
	 * @return bool returns false if no rendering occurred
	 */
	function render(IRequest $Request, $sendHeaders=true) {
		$MimeType = $Request->getMimeType();

		if($sendHeaders) {
			$Renderer = $this->mRenderer;
			if($Renderer instanceof IHeaderResponse) {
				$Renderer->sendHeaders($Request, $MimeType->getName());
			} elseif($Renderer instanceof IResponse) {
				$Util = new ResponseUtil($Renderer);
				$Util->sendHeaders($Request, $MimeType->getName());
			}
		}

		if($MimeType instanceof HTMLMimeType) {
			$this->renderHTML($Request);

		} elseif($MimeType instanceof XMLMimeType) {
			$this->renderXML($Request);

		} elseif($MimeType instanceof JSONMimeType) {
			$this->renderJSON($Request);

		} elseif($MimeType instanceof TextMimeType) {
			$this->renderText($Request);

		} elseif($MimeType instanceof UnknownMimeType) {
			return false;
		}

		return true;
	}

	/**
	 * Render request as html
	 * @param IRequest $Request the IRequest instance for this render which contains the request and remaining args
	 * @param IAttributes $Attr
	 * @return String|void always returns void
	 */
	function renderHTML(IRequest $Request, IAttributes $Attr = null) {
		/** @var IRenderHTML $Renderer */
		$Template = new HTMLResponseBody();
		if($this->mRenderer instanceof IRenderHTML) {
			$Template->renderHTMLContent($Request, $this->mRenderer, $Attr);
		} else {
			$Util = new ResponseUtil($this->mRenderer);
			$Template->renderHTMLContent($Request, $Util, $Attr);
		}
	}

	/**
	 * Render request as JSON
	 * @param \CPath\Request\IRequest $Request the IRequest instance for this render which contains the request and remaining args
	 * @return String|void always returns void
	 */
	function renderJSON(IRequest $Request) {
		if($this->mRenderer instanceof IRenderJSON) {
			$this->mRenderer->renderJSON($Request);
		} else {
			$Util = new ResponseUtil($this->mRenderer);
			$Util->renderJSON($Request);
		}
	}

	/**
	 * Render request as plain text
	 * @param IRequest $Request the IRequest instance for this render which contains the request and remaining args
	 * @return String|void always returns void
	 */
	function renderText(IRequest $Request) {
		if($this->mRenderer instanceof IRenderText) {
			$this->mRenderer->renderText($Request);
		} else {
			$Util = new ResponseUtil($this->mRenderer);
			$Util->renderText($Request);
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
		if($this->mRenderer instanceof IRenderXML) {
			$this->mRenderer->renderXML($Request, $rootElementName, $declaration);
		} else {
			$Util = new ResponseUtil($this->mRenderer);
			$Util->renderXML($Request, $rootElementName, $declaration);
		}
	}

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
		if (
			($Previous instanceof IRenderHTML && $Request->getMimeType() instanceof HTMLMimeType) ||
			($Previous instanceof IRenderText && $Request->getMimeType() instanceof TextMimeType) ||
			($Previous instanceof IRenderJSON && $Request->getMimeType() instanceof JSONMimeType) ||
			($Previous instanceof IRenderXML  && $Request->getMimeType() instanceof XMLMimeType)  ||
			($Previous instanceof IResponse)
		) {
			$Handler = new RenderHandler($Previous);
			$Handler->render($Request);
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