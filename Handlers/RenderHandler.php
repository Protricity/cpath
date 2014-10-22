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
use CPath\Data\Map\IKeyMap;
use CPath\Data\Map\ISequenceMap;
use CPath\Data\Map\KeyMapRenderer;
use CPath\Data\Map\SequenceMapRenderer;
use CPath\Framework\Render\Header\IHeaderWriter;
use CPath\Render\HTML\Header\IHTMLSupportHeaders;
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
use CPath\Request\Exceptions\RequestException;
use CPath\Request\IRequest;
use CPath\Request\MimeType\IRequestedMimeType;
use CPath\Request\MimeType\MimeType;
use CPath\Request\MimeType\UnknownMimeType;
use CPath\Response\Common\ExceptionResponse;
use CPath\Response\IHeaderResponse;
use CPath\Response\IResponse;
use CPath\Route\DefaultMap;
use CPath\Route\IRoute;
use CPath\Route\RouteBuilder;
use CPath\Route\RouteIndex;

class RenderHandler implements IRoute, IBuildable, IRenderHTML, IRenderText, IRenderJSON, IRenderXML
{
	private $mObject;
	public function __construct($Object) {
		$this->mObject = $Object;
	}

	public function getObject() {
		return $this->mObject;
	}

	/**
	 * @param \CPath\Request\MimeType\IRequestedMimeType $MimeType
	 * @return IRenderHTML|IRenderXML|IRenderJSON|IRenderText
	 */
	public function getRenderer(IRequestedMimeType $MimeType) {
		$Object = $this->getObject();
		if ($Object instanceof IRenderHTML && $MimeType instanceof HTMLMimeType) {
			return $Object;

		} elseif ($Object instanceof IRenderText && $MimeType instanceof TextMimeType) {
			return $Object;

		} elseif ($Object instanceof IRenderJSON && $MimeType instanceof JSONMimeType) {
			return $Object;

		} elseif ($Object instanceof IRenderXML && $MimeType instanceof XMLMimeType) {
			return $Object;

		} elseif ($Object instanceof IKeyMap) {
			return new KeyMapRenderer($Object);

		} elseif ($Object instanceof ISequenceMap) {
			return new SequenceMapRenderer($Object);

		} elseif ($Object instanceof IResponse) {
			return new ResponseUtil($Object);

		} elseif ($Object instanceof \Exception) {
			$Object = new ExceptionResponse($Object);
			return new ResponseUtil($Object);

		} else {
			if(is_scalar($Object))
				$Exception = new RequestException("Scalar could not be rendered: " . gettype($Object));
			else
				$Exception = new RequestException("Object could not be rendered: " . get_class($Object));
			return new ResponseUtil($Exception);
		}
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
		$Renderer = $this->getRenderer($Request->getMimeType());
		if($Renderer instanceof IHTMLSupportHeaders)
			$Renderer->writeHeaders($Request, $Head);
	}

	function sendHeaders(IRequest $Request) {
		$MimeType = $Request->getMimeType();

		$Renderer = $this->mObject;
		if($Renderer instanceof IHeaderResponse) {
			$Renderer->sendHeaders($Request, $MimeType->getName());

		} elseif($Renderer instanceof IResponse) {
			$Util = new ResponseUtil($Renderer);
			$Util->sendHeaders($Request, $MimeType->getName());
		}
	}

	/**
	 * Renders a response object or returns false
	 * @param IRequest $Request the IRequest instance for this render
	 * @param bool $sendHeaders
	 * @internal param \CPath\Request\Executable\IExecutable|\CPath\Response\IResponse $Response
	 * @return bool returns false if no rendering occurred
	 */
	function render(IRequest $Request, $sendHeaders=true) {
		if($sendHeaders)
			$this->sendHeaders($Request);

		$MimeType = $Request->getMimeType();

		if ($MimeType instanceof HTMLMimeType) {
			$this->renderHTML($Request);

		} elseif ($MimeType instanceof XMLMimeType) {
			$this->renderXML($Request);

		} elseif ($MimeType instanceof JSONMimeType) {
			$this->renderJSON($Request);

		} elseif ($MimeType instanceof TextMimeType) {
			$this->renderText($Request);

		} elseif ($MimeType instanceof UnknownMimeType) {
			return false;

		}

		return true;
	}

	/**
	 * Render request as html
	 * @param IRequest $Request the IRequest instance for this render which contains the request and remaining args
	 * @param IAttributes $Attr
	 * @param bool $sendHeaders
	 * @return String|void always returns void
	 */
	function renderHTML(IRequest $Request, IAttributes $Attr = null, $sendHeaders=false) {
		if($sendHeaders)
			$this->sendHeaders($Request);

		try {
			$Render = $this->getRenderer($Request->getMimeType());
			$Render->renderHTML($Request, $Attr);
//			$Template = new HTMLResponseBody();
//			$Template->addContent($this->getRenderer($Request->getMimeType()));
//			$Template->renderHTML($Request, $Attr);

		} catch (\Exception $ex) {
			$Render = $this->getRenderer($ex);
			$Render->renderHTML($Request, $Attr);
//			$Template = new HTMLResponseBody();
//			$Handler = new RenderHandler($ex);
//			$Template->addContent($Handler);
//			$Template->renderHTML($Request, $Attr);
		}
	}

	/**
	 * Render request as JSON
	 * @param \CPath\Request\IRequest $Request the IRequest instance for this render which contains the request and remaining args
	 * @param bool $sendHeaders
	 * @return String|void always returns void
	 */
	function renderJSON(IRequest $Request, $sendHeaders=false) {
		if($sendHeaders)
			$this->sendHeaders($Request);

		try {
			$Renderer = $this->getRenderer($Request->getMimeType());
			$Renderer->renderJSON($Request);

		} catch (\Exception $ex) {
			$Renderer = new RenderHandler($ex);
			$Renderer->renderJSON($Request, false);
		}
	}

	/**
	 * Render request as plain text
	 * @param IRequest $Request the IRequest instance for this render which contains the request and remaining args
	 * @param bool $sendHeaders
	 * @return String|void always returns void
	 */
	function renderText(IRequest $Request, $sendHeaders=false) {
		if($sendHeaders)
			$this->sendHeaders($Request);

		try {
			$Renderer = $this->getRenderer($Request->getMimeType());
			$Renderer->renderText($Request);

		} catch (\Exception $ex) {
			$Renderer = new RenderHandler($ex);
			$Renderer->renderText($Request, false);
		}
	}

	/**
	 * Render request as xml
	 * @param \CPath\Request\IRequest $Request the IRequest instance for this render which contains the request and remaining args
	 * @param string $rootElementName Optional name of the root element
	 * @param bool $declaration if true, the <!xml...> declaration will be rendered
	 * @param bool $sendHeaders
	 * @return String|void always returns void
	 */
	function renderXML(IRequest $Request, $rootElementName = 'root', $declaration = false, $sendHeaders=false) {
		if($sendHeaders)
			$this->sendHeaders($Request);

		try {
			$Renderer = $this->getRenderer($Request->getMimeType());
			$Renderer->renderXML($Request, $rootElementName, $declaration);

		} catch (\Exception $ex) {
			$Renderer = new RenderHandler($ex);
			$Renderer->renderXML($Request, $rootElementName, $declaration);
		}
	}

	// Static

	/**
	 * Route the request to this class object and return the object
	 * @param IRequest $Request the IRequest instance for this render
	 * @param Object[]|null $Previous all previous response object that were passed from a handler, if any
	 * @param null|mixed $_arg [varargs] passed by route map
	 * @throws \Exception
	 * @return void|bool|Object returns a response object
	 * If nothing is returned (or bool[true]), it is assumed that rendering has occurred and the request ends
	 * If false is returned, this static handler will be called again if another handler returns an object
	 * If an object is returned, it is passed along to the next handler
	 */
	static function routeRequestStatic(IRequest $Request, Array $Previous=array(), $_arg=null) {
		//static $failSafe = false;

		if(sizeof($Previous) === 0) {
			$Map = new DefaultMap();
			$routePrefix = 'GET ' . $Request->getPath();
			$Previous[] = new RouteIndex($Map, $routePrefix);
		}

		$Handler = new RenderHandler(reset($Previous));
		$Handler->render($Request);
		return true;
//
//		$Render = reset($Previous); // TODO: multi render?
//		if (
//			($Render instanceof IRenderHTML && $Request->getMimeType() instanceof HTMLMimeType) ||
//			($Render instanceof IRenderText && $Request->getMimeType() instanceof TextMimeType) ||
//			($Render instanceof IRenderJSON && $Request->getMimeType() instanceof JSONMimeType) ||
//			($Render instanceof IRenderXML  && $Request->getMimeType() instanceof XMLMimeType)  ||
//			($Render instanceof IResponse)
//		) {
//			try {
//				$Handler = new RenderHandler($Render);
//				$Handler->render($Request);
//				return true;
//
//			} catch (IResponse $Response) {
//			} catch (\Exception $ex) {
//				$Response = new ExceptionResponse($ex);
//			}
//
//			if($failSafe)
//				throw $Response;
//			$failSafe = true;
//
//			$Renderer = new ResponseUtil($Response);
//			$Handler = new RenderHandler($Renderer);
//			$Handler->render($Request);
//			return true;
//
//		} elseif ($Render instanceof IRenderHTML) {
//		} elseif ($Render instanceof IRenderText) {
//		} elseif ($Render instanceof IRenderJSON) {
//		} elseif ($Render instanceof IRenderXML) {
//
//		} elseif ($Render instanceof IKeyMap) {
//			return $this->renderSequenceMap($Render);
//
//		} elseif ($Render instanceof ISequenceMap) {
//			$Renderer = new SequenceMapRenderer($Render);
//			$Handler = new RenderHandler($Renderer);
//			$Handler->render($Request);
//			return true;
//
//		} elseif ($Render instanceof IResponse) {
//			$Renderer = new ResponseUtil($Render);
//			$Handler = new RenderHandler($Renderer);
//			$Handler->render($Request);
//			return true;
//
//		} elseif ($Render instanceof \Exception) {
//			$Render = new ExceptionResponse($Render);
//			$Renderer = new ResponseUtil($Render);
//			$Handler = new RenderHandler($Renderer);
//			$Handler->render($Request);
//			return true;
//
//		}
//
//		return false;
	}

	/**
	 * Handle this request and render any content
	 * @param IBuildRequest $Request the build request instance for this build session
	 * @return String|void always returns void
	 * @build --disable 0
	 * Note: Use doctag 'build' with '--disable 1' to have this IBuildable class skipped during a build
	 */
	static function handleStaticBuild(IBuildRequest $Request) {
		$RouteBuilder = new RouteBuilder($Request, new DefaultMap(), '__render');
		$RouteBuilder->writeRoute('ANY *', __CLASS__);
	}
}