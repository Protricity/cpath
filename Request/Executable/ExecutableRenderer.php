<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/26/14
 * Time: 8:33 PM
 */
namespace CPath\Request\Executable;

use CPath\Render\HTML\Attribute;
use CPath\Render\HTML\Attribute\IAttributes;
use CPath\Render\HTML\Header\IHeaderWriter;
use CPath\Render\HTML\Header\IHTMLSupportHeaders;
use CPath\Render\HTML\IRenderHTML;
use CPath\Render\JSON\IRenderJSON;
use CPath\Render\Text\IRenderText;
use CPath\Render\XML\IRenderXML;
use CPath\Request\Form\IFormRequest;
use CPath\Request\IRequest;
use CPath\Request\Web\WebFormRequest;
use CPath\Request\Web\WebRequest;
use CPath\Response\Common\ExceptionResponse;
use CPath\Response\IResponse;
use CPath\Response\IResponseHeaders;
use CPath\Response\Response;
use CPath\Response\ResponseRenderer;


class ExecutableRenderer implements IResponse, IResponseHeaders, IRenderHTML, IRenderJSON, IRenderXML, IRenderText, IHTMLSupportHeaders, IExecutable {

	private $mExecutable;
	/** @var IResponse */
	private $mResponse=null;
    public function __construct(IExecutable $Executable) {
        $this->mExecutable = $Executable;
    }

	/**
	 * Execute a command and return a response. Does not render
	 * @param IRequest $Request
	 * @return IResponse the execution response
	 */
	function execute(IRequest $Request) {
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
	 * @return String|void always returns void
	 */
	function renderHTML(IRequest $Request, IAttributes $Attr = null, IRenderHTML $Parent = null) {
		if($this->mExecutable instanceof IRenderHTML) {
			$this->mExecutable->renderHTML($Request, $Attr, $Parent);
			return;
		}

		$Response = $this->getResponse($Request);

		if($Response instanceof ExceptionResponse
			&& $Request instanceof WebFormRequest) {
			$WebRequest = new WebRequest($Request->getMethodName(), $Request->getPath(), $Request->getParameterValues());
			$Response = $this->execute($WebRequest);
		}

		if(!$Response instanceof IRenderHTML)
			$Response = new ResponseRenderer($Response);

		$Response->renderHTML($Request, $Attr);
		unset($this->mResponse);
	}

	/**
	 * Render request as JSON
	 * @param \CPath\Request\IRequest $Request the IRequest inst for this render which contains the request and remaining args
	 * @return String|void always returns void
	 */
	function renderJSON(IRequest $Request) {
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
	 * @return String|void always returns void
	 */
	function renderText(IRequest $Request) {
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
	 * @return String|void always returns void
	 */
	function renderXML(IRequest $Request, $rootElementName = 'root', $declaration = false) {
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
}