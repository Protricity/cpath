<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/26/14
 * Time: 8:33 PM
 */
namespace CPath\Request\Executable;

use CPath\Build\IBuildable;
use CPath\Build\IBuildRequest;
use CPath\Render\HTML\Attribute;
use CPath\Render\HTML\Attribute\IAttributes;
use CPath\Render\HTML\Header\IHeaderWriter;
use CPath\Render\HTML\Header\IHTMLSupportHeaders;
use CPath\Render\HTML\HTMLMimeType;
use CPath\Render\HTML\IRenderHTML;
use CPath\Render\IRenderAll;
use CPath\Render\JSON\IRenderJSON;
use CPath\Render\JSON\JSONMimeType;
use CPath\Render\Text\IRenderText;
use CPath\Render\Text\TextMimeType;
use CPath\Render\XML\IRenderXML;
use CPath\Render\XML\XMLMimeType;
use CPath\Request\Form\IFormRequest;
use CPath\Request\IRequest;
use CPath\Request\NonFormRequestWrapper;
use CPath\Request\Web\WebFormRequest;
use CPath\Response\Common\ExceptionResponse;
use CPath\Response\IResponse;
use CPath\Response\IResponseHeaders;
use CPath\Response\Response;
use CPath\Response\ResponseRenderer;
use CPath\Route\CPathMap;
use CPath\Route\IRoutable;
use CPath\Route\RouteBuilder;


class ExecutableRenderer implements IResponse, IResponseHeaders, IRenderAll, IHTMLSupportHeaders, IExecutable, IRoutable, IBuildable {

	private $mExecutable;
	/** @var IResponse */
	private $mResponse=null;
	private $mUseFormRequest = false;
    public function __construct(IExecutable $Executable, $useFormRequest=false) {
        $this->mExecutable = $Executable;
	    $this->mUseFormRequest = $useFormRequest;
    }

	/**
	 * Execute a command and return a response. Does not render
	 * @param IRequest $Request
	 * @return IResponse the execution response
	 */
	function execute(IRequest $Request) {
		if($Request instanceof WebFormRequest && !$this->mUseFormRequest)
			$Request = $Request->getWebRequest();
		if($Request instanceof IFormRequest && !$this->mUseFormRequest)
			$Request = new NonFormRequestWrapper($Request);

		try {
			$Response = $this->mExecutable->execute($Request)
			?: new Response("No Response", IResponse::HTTP_ERROR);

		} catch (\Exception $ex) {
			$Response = $ex;
			if(!$Response instanceof IResponse)
				$Response = new ExceptionResponse($ex);
		}

		$this->mResponse = $Response;
		return $Response;
	}

	public function getResponse(IRequest $Request=null) {
		if($this->mResponse)
			return $this->mResponse;

		if(!$Request)
			return $this;

		$this->mResponse = $this->execute($Request);
		return $this->mResponse;
	}

	/**
	 * Send response headers for this response
	 * @param IRequest $Request
	 * @param string $mimeType
	 * @return bool returns true if the headers were sent, false otherwise
	 */
	function sendHeaders(IRequest $Request, $mimeType = null) {
		$Response = $this->getResponse($Request);
		if(!$Response instanceof IResponseHeaders)
			$Response = new ResponseRenderer($Response);
		return $Response->sendHeaders($Request, $mimeType);
	}

	/**
	 * Write all support headers used by this renderer
	 * @param IRequest $Request
	 * @param \CPath\Render\HTML\Header\IHeaderWriter $Head the writer inst to use
	 * @return void
	 */
	function writeHeaders(IRequest $Request, IHeaderWriter $Head) {
		$Response = $this->getResponse($Request);
		if($Response instanceof IHTMLSupportHeaders)
			$Response->writeHeaders($Request, $Head);
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

		if($this->mExecutable instanceof IRenderHTML) {
			$this->mExecutable->renderHTML($Request, $Attr, $Parent);
			return;
		}

		$Response = $this->getResponse($Request);

//		if($Response instanceof ExceptionResponse
//			&& $Request instanceof WebFormRequest) {
//			$WebRequest = new WebRequest($Request->getMethodName(), $Request->getPath(), $Request->getParameterValues());
//			$Response = $this->execute($WebRequest);
//		}

		if(!$Response instanceof IRenderHTML)
			$Response = new ResponseRenderer($Response);

		$Response->renderHTML($Request, $Attr);
		unset($this->mResponse);
	}

	/**
	 * Render request as JSON
	 * @param \CPath\Request\IRequest $Request the IRequest inst for this render which contains the request and remaining args
	 * @param bool $sendHeaders
	 * @return String|void always returns void
	 */
	function renderJSON(IRequest $Request, $sendHeaders = true) {
		if ($sendHeaders)
			$this->sendHeaders($Request);

		if($this->mExecutable instanceof IRenderJSON) {
			$this->mExecutable->renderJSON($Request);
			return;
		}
		$Response = $this->getResponse($Request);
		if(!$Response instanceof IRenderJSON)
			$Response = new ResponseRenderer($Response);
		$Response->renderJSON($Request);
	}

	/**
	 * Render request as plain text
	 * @param IRequest $Request the IRequest inst for this render which contains the request and remaining args
	 * @param bool $sendHeaders
	 * @return String|void always returns void
	 */
	function renderText(IRequest $Request, $sendHeaders = true) {
		if ($sendHeaders)
			$this->sendHeaders($Request);

		if($this->mExecutable instanceof IRenderText) {
			$this->mExecutable->renderText($Request);
			return;
		}
		$Response = $this->getResponse($Request);
		if(!$Response instanceof IRenderText)
			$Response = new ResponseRenderer($Response);
		$Response->renderText($Request);
	}

	/**
	 * Render request as xml
	 * @param \CPath\Request\IRequest $Request the IRequest inst for this render which contains the request and remaining args
	 * @param string $rootElementName Optional name of the root element
	 * @param bool $declaration if true, the <!xml...> declaration will be rendered
	 * @param bool $sendHeaders
	 * @return String|void always returns void
	 */
	function renderXML(IRequest $Request, $rootElementName = 'root', $declaration = false, $sendHeaders = true) {
		if ($sendHeaders)
			$this->sendHeaders($Request);

		if($this->mExecutable instanceof IRenderXML) {
			$this->mExecutable->renderXML($Request);
			return;
		}
		$Response = $this->getResponse($Request);
		if(!$Response instanceof IRenderXML)
			$Response = new ResponseRenderer($Response);
		$Response->renderXML($Request);
	}

	/**
	 * Renders a response object or returns false
	 * @param IRequest $Request the IRequest inst for this render
	 * @param bool $sendHeaders
	 * @return bool returns false if no rendering occurred
	 */
	function render(IRequest $Request, $sendHeaders = true) {
		$MimeType = $Request->getMimeType();

		if ($MimeType instanceof HTMLMimeType) {
			$this->renderHTML($Request, null, $this, $sendHeaders);
			return true;
		}
		if ($MimeType instanceof XMLMimeType) {
			$this->renderXML($Request, $sendHeaders);
			return true;
		}
		if ($MimeType instanceof JSONMimeType) {
			$this->renderJSON($Request, $sendHeaders);
			return true;
		}
		if ($MimeType instanceof TextMimeType) {
			$this->renderText($Request, $sendHeaders);
			return true;
		}
		return false;
	}

	/**
	 * Get the request status code
	 * @return int
	 */
	function getCode() {
		return $this->mResponse
			? $this->mResponse->getCode()
			: IResponse::HTTP_ERROR;
	}

	/**
	 * Get the IResponse Message
	 * @return String
	 */
	function getMessage() {
		return $this->mResponse
			? $this->mResponse->getMessage()
			: "No Execution";
	}

	// Static

	/**
	 * Handle this request and render any content
	 * @param IBuildRequest $Request the build request inst for this build session
	 * @return void
	 * @build --disable 0
	 * Note: Use doctag 'build' with '--disable 1' to have this IBuildable class skipped during a build
	 */
	static function handleBuildStatic(IBuildRequest $Request) {
		$RouteBuilder = new RouteBuilder($Request, new CPathMap(), '_executable');
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
		if(sizeof($Previous) === 0
			|| !$Previous[0] instanceof IExecutable) {
			return false;
		}

		$Previous[0] = new ExecutableRenderer($Previous[0], true);
		return $Previous[0];
	}
}