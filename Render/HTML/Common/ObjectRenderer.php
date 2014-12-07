<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 11/25/14
 * Time: 5:04 PM
 */
namespace CPath\Render\HTML\Common;

use CPath\Build\IBuildable;
use CPath\Build\IBuildRequest;
use CPath\Data\Map\IKeyMap;
use CPath\Data\Map\ISequenceMap;
use CPath\Data\Map\KeyMapRenderer;
use CPath\Data\Map\SequenceMapRenderer;
use CPath\Render\HTML\Attribute\IAttributes;
use CPath\Render\HTML\Header\IHeaderWriter;
use CPath\Render\HTML\Header\IHTMLSupportHeaders;
use CPath\Render\HTML\HTMLMimeType;
use CPath\Render\HTML\IHTMLContainer;
use CPath\Render\HTML\IHTMLContainerItem;
use CPath\Render\HTML\IRenderHTML;
use CPath\Render\IRenderAll;
use CPath\Render\JSON\IRenderJSON;
use CPath\Render\JSON\JSONMimeType;
use CPath\Render\Text\IRenderText;
use CPath\Render\Text\TextMimeType;
use CPath\Render\XML\IRenderXML;
use CPath\Render\XML\XMLMimeType;
use CPath\Request\Exceptions\RequestException;
use CPath\Request\Executable\ExecutableRenderer;
use CPath\Request\Executable\IExecutable;
use CPath\Request\IRequest;
use CPath\Request\MimeType\IRequestedMimeType;
use CPath\Request\MimeType\UnknownMimeType;
use CPath\Response\Common\ExceptionResponse;
use CPath\Response\IResponse;
use CPath\Response\IResponseHeaders;
use CPath\Response\ResponseRenderer;
use CPath\Route\CPathMap;
use CPath\Route\IRoutable;
use CPath\Route\RouteBuilder;

class ObjectRenderer implements IRenderAll, IHTMLSupportHeaders, IRoutable, IBuildable, IHTMLContainerItem
{

	private $mObject;
	/** @var ExecutableRenderer */
	private $mExecutableRenderer = null;

	public function __construct($Object) {
		$this->mObject = $Object;
	}

	/**
	 * @param IRequestedMimeType $MimeType
	 * @return IRenderHTML|IRenderText|IRenderXML|IRenderJSON
	 */
	public function getRenderableObject(IRequestedMimeType $MimeType = null) {
		$Object = $this->mObject;

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

		} elseif ($Object instanceof IExecutable) {
			return $this->mExecutableRenderer
				?: $this->mExecutableRenderer = new ExecutableRenderer($Object);

		} elseif ($Object instanceof IResponse) {
			return new ResponseRenderer($Object);

		} elseif ($Object instanceof \Exception) {
			$Object = new ExceptionResponse($Object);

			return new ResponseRenderer($Object);

		} else {
			if (is_scalar($Object))
				$Exception = new RequestException("Scalar could not be rendered: " . gettype($Object));
			else
				$Exception = new RequestException("Object could not be rendered: " . get_class($Object));

			return new ResponseRenderer($Exception);
		}
	}

	/**
	 * Write all support headers used by this IView inst
	 * @param IRequest $Request
	 * @param IHeaderWriter $Head the writer inst to use
	 * @return void
	 */
	function writeHeaders(IRequest $Request, IHeaderWriter $Head) {
		$Renderer = $this->mObject;
		if ($Renderer instanceof IHTMLSupportHeaders)
			$Renderer->writeHeaders($Request, $Head);

		$Renderer = $this->getRenderableObject($Request->getMimeType());
		if ($Renderer !== $this->mObject && $Renderer instanceof IHTMLSupportHeaders)
			$Renderer->writeHeaders($Request, $Head);
	}

	function sendHeaders(IRequest $Request) {
		$MimeType = $Request->getMimeType();

		$Renderer = $this->mObject;
		if ($Renderer instanceof IResponseHeaders) {
			$Renderer->sendHeaders($Request, $MimeType->getName());

		} elseif ($Renderer instanceof IResponse) {
			$Util = new ResponseRenderer($Renderer);
			$Util->sendHeaders($Request, $MimeType->getName());

		} elseif ($Renderer instanceof IExecutable) {
			$Renderer = $this->mExecutableRenderer
				?: $this->mExecutableRenderer = new ExecutableRenderer($this->mObject);
			$Renderer->sendHeaders($Request, $MimeType->getName());

		} else {
			$Request->log("No response provided with render object: " . get_class($Renderer), $Request::WARNING);
		}
	}

	/**
	 * Renders a response object or returns false
	 * @param IRequest $Request the IRequest inst for this render
	 * @param bool $sendHeaders
	 * @return bool returns false if no rendering occurred
	 */
	function render(IRequest $Request, $sendHeaders = true) {
//		if ($sendHeaders)
//			$this->sendHeaders($Request);

		$MimeType = $Request->getMimeType();

		if ($MimeType instanceof HTMLMimeType) {
			$this->renderHTML($Request, null, $this, $sendHeaders);

		} elseif ($MimeType instanceof XMLMimeType) {
			$this->renderXML($Request, $sendHeaders);

		} elseif ($MimeType instanceof JSONMimeType) {
			$this->renderJSON($Request, $sendHeaders);

		} elseif ($MimeType instanceof TextMimeType) {
			$this->renderText($Request, $sendHeaders);

		} elseif ($MimeType instanceof UnknownMimeType) {
			return false;

		}

		return true;
	}

	/**
	 * Render request as html
	 * @param IRequest $Request the IRequest inst for this render which contains the request and remaining args
	 * @param IAttributes $Attr
	 * @param IRenderHTML $Parent
	 * @param bool $sendHeaders
	 * @return String|void always returns void
	 */
	function renderHTML(IRequest $Request, IAttributes $Attr = null, IRenderHTML $Parent = null, $sendHeaders = true) {
		if ($sendHeaders)
			$this->sendHeaders($Request);

		try {
			$Renderer = $this->getRenderableObject($Request->getMimeType());
			$Renderer->renderHTML($Request, $Attr, $Parent);

		} catch (IRenderHTML $ex) {
			$ex->renderHTML($Request);

		} catch (IResponse $ex) {
			$Renderer = new ResponseRenderer($ex);
			$Renderer->renderHTML($Request, $Attr, $Parent);

		} catch (\Exception $ex) {
			$Response = new ExceptionResponse($ex);
			$Renderer = new ResponseRenderer($Response);
			$Renderer->renderHTML($Request, $Attr, $Parent);
		}
	}

	/**
	 * Render request as JSON
	 * @param \CPath\Request\IRequest $Request the IRequest inst for this render which contains the request and remaining args
	 * @param bool $sendHeaders
	 * @return String|void always returns void
	 */
	function renderJSON(IRequest $Request, $sendHeaders = false) {
		if ($sendHeaders)
			$this->sendHeaders($Request);

		try {
			$Renderer = $this->getRenderableObject($Request->getMimeType());
			$Renderer->renderJSON($Request);

		} catch (IResponse $ex) {
			$Renderer = new ResponseRenderer($ex);
			$Renderer->renderJSON($Request);

		} catch (\Exception $ex) {
			$Response = new ExceptionResponse($ex);
			$Renderer = new ResponseRenderer($Response);
			$Renderer->renderJSON($Request);
		}
	}

	/**
	 * Render request as plain text
	 * @param IRequest $Request the IRequest inst for this render which contains the request and remaining args
	 * @param bool $sendHeaders
	 * @return String|void always returns void
	 */
	function renderText(IRequest $Request, $sendHeaders = false) {
		if ($sendHeaders)
			$this->sendHeaders($Request);

		try {
			$Renderer = $this->getRenderableObject($Request->getMimeType());
			$Renderer->renderText($Request);

		} catch (IResponse $ex) {
			$Renderer = new ResponseRenderer($ex);
			$Renderer->renderText($Request);

		} catch (\Exception $ex) {
			$Response = new ExceptionResponse($ex);
			$Renderer = new ResponseRenderer($Response);
			$Renderer->renderText($Request);
		}
	}

	/**
	 * Render request as xml
	 * @param \CPath\Request\IRequest $Request the IRequest inst for this render which contains the request and remaining args
	 * @param string $rootElementName Optional name of the root element
	 * @param bool $declaration if true, the <!xml...> declaration will be rendered
	 * @param bool $sendHeaders
	 * @return String|void always returns void
	 */
	function renderXML(IRequest $Request, $rootElementName = 'root', $declaration = false, $sendHeaders = false) {
		if ($sendHeaders)
			$this->sendHeaders($Request);

		try {
			$Renderer = $this->getRenderableObject($Request->getMimeType());
			$Renderer->renderXML($Request, $rootElementName, $declaration);

		} catch (IResponse $ex) {
			$Renderer = new ResponseRenderer($ex);
			$Renderer->renderXML($Request, $rootElementName, $declaration);

		} catch (\Exception $ex) {
			$Response = new ExceptionResponse($ex);
			$Renderer = new ResponseRenderer($Response);
			$Renderer->renderXML($Request, $rootElementName, $declaration);
		}
	}


	// Static

	/**
	 * Route the request to this class object and return the object
	 * @param IRequest $Request the IRequest inst for this render
	 * @param Object[]|null $Previous all previous response object that were passed from a handler, if any
	 * @param null|mixed $_arg [varargs] passed by route map
	 * @throws \Exception
	 * @return void|bool|Object returns a response object
	 * If nothing is returned (or bool[true]), it is assumed that rendering has occurred and the request ends
	 * If false is returned, this static handler will be called again if another handler returns an object
	 * If an object is returned, it is passed along to the next handler
	 */
	static function routeRequestStatic(IRequest $Request, Array $Previous=array(), $_arg=null) {
		if(sizeof($Previous) === 0) {
			return false;
		}
//
//		$Object = reset($Previous);
//		$Handler = null;
//		foreach($Previous as $Object) {
//			if($Object instanceof ObjectRenderer) {
//				foreach($Previous as $Object) {
//					if(!$Object instanceof IRenderAll)
//						$Object = new ObjectRenderer($Object);
//					$Object->render()
//				}
//				$Object->render($Request);
//				return true;
//		}

		$Object = reset($Previous);
		if(!$Object instanceof IRenderAll)
			$Object = new ObjectRenderer($Object);
		$Object->render($Request);
		return true;
	}

	/**
	 * Handle this request and render any content
	 * @param IBuildRequest $Request the build request inst for this build session
	 * @return void
	 * @build --disable 0
	 * Note: Use doctag 'build' with '--disable 1' to have this IBuildable class skipped during a build
	 */
	static function handleStaticBuild(IBuildRequest $Request) {
		$RouteBuilder = new RouteBuilder($Request, new CPathMap(), '__render');
		$RouteBuilder->writeRoute('ANY *', __CLASS__);
	}

	/**
	 * Called when item is added to an IHTMLContainer
	 * @param IHTMLContainer $Parent
	 * @return void
	 */
	function onContentAdded(IHTMLContainer $Parent) {
		if($this->mObject instanceof IHTMLContainerItem)
			$this->mObject->onContentAdded($Parent);
	}
}