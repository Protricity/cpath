<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Response;
use CPath\Data\Map\IKeyMap;
use CPath\Data\Map\IKeyMapper;
use CPath\Render\HTML\Attribute\IAttributes;
use CPath\Render\HTML\Header\IHeaderWriter;
use CPath\Render\HTML\Header\IHTMLSupportHeaders;
use CPath\Render\HTML\HTMLKeyMapRenderer;
use CPath\Render\HTML\HTMLMimeType;
use CPath\Render\HTML\IRenderHTML;
use CPath\Render\IRenderAll;
use CPath\Render\JSON\IRenderJSON;
use CPath\Render\JSON\JSONKeyMapRenderer;
use CPath\Render\JSON\JSONMimeType;
use CPath\Render\Text\IRenderText;
use CPath\Render\Text\TextKeyMapRenderer;
use CPath\Render\Text\TextMimeType;
use CPath\Render\XML\IRenderXML;
use CPath\Render\XML\XMLKeyMapRenderer;
use CPath\Render\XML\XMLMimeType;
use CPath\Request\IRequest;
use CPath\Request\MimeType\UnknownMimeType;
use CPath\Response\Common\ExceptionResponse;

final class ResponseRenderer implements IKeyMap, IRenderAll, IHeaderResponse, IHTMLSupportHeaders {
    private $mResponse;
    private $mSent = false;
    //private $mContainer;

	/**
	 * @param IResponse|\Exception $Response
	 */
	function __construct($Response) {
        $this->mResponse = $Response;
        //$this->mContainer = $HTMLContainer;
    }

	function getResponse() {
		$Response = $this->mResponse;
		if(($Response instanceof \Exception) && (!$Response instanceof IResponse))
			$Response = new ExceptionResponse($Response);
		else if(!is_object($Response))
			$Response = new Response("Not a response object: " . var_export($Response, true), false);
		else if(!$Response instanceof IResponse)
			$Response = new Response("Invalid Response Object: " . get_class($Response), false);
		return $Response;
	}

	/**
	 * Get the request status code
	 * @return int
	 */
	function getCode() {
		return $this->getResponse()->getCode();
	}

	/**
	 * Get the IResponse Message
	 * @return String
	 */
	function getMessage() {
		return $this->getResponse()->getMessage();
	}

    /**
     * Send response headers for this response
     * @param IRequest $Request
     * @param string $mimeType
     * @return bool returns true if the headers were sent, false otherwise
     */
    function sendHeaders(IRequest $Request, $mimeType = null) {
        if($this->mSent || headers_sent())
            return false;

        header("HTTP/1.1 " . $this->getCode() . " " . preg_replace('/[^\w -]/', '', $this->getMessage()));
        header("MainContent-Type: " . $mimeType);

        header('Access-Control-Allow-Origin: *');

        $this->mSent = true;
        return true;
    }

	/**
	 * Map data to a data map
	 * @param IKeyMapper $Map the map inst to add data to
	 * @internal param \CPath\Request\IRequest $Request
	 * @internal param \CPath\Request\IRequest $Request
	 * @return void
	 */
    function mapKeys(IKeyMapper $Map) {
	    if($this->mResponse instanceof IKeyMap) {
		    $this->mResponse->mapKeys($Map);
	    } else {
		    $Map->map(IResponse::STR_MESSAGE, $this->getMessage());
		    $Map->map(IResponse::STR_CODE, $this->getCode());
	    }
    }

	/**
	 * Renders a response object or returns false
	 * @param IRequest $Request the IRequest inst for this render
	 * @param bool $sendHeaders if true, sends the response headers
	 * @return bool returns false if no rendering occurred
	 */
	function render(IRequest $Request, $sendHeaders=true) {
		if($sendHeaders)
			$this->sendHeaders($Request);

		$MimeType = $Request->getMimeType();

		if ($MimeType instanceof HTMLMimeType) {
			$this->renderHTML($Request, false);

		} elseif ($MimeType instanceof XMLMimeType) {
			$this->renderXML($Request, false);

		} elseif ($MimeType instanceof JSONMimeType) {
			$this->renderJSON($Request, false);

		} elseif ($MimeType instanceof TextMimeType) {
			$this->renderText($Request, false);

		} elseif ($MimeType instanceof UnknownMimeType) {
			return false;

		}

		return true;
	}

	/**
	 * Write all support headers used by this renderer
	 * @param IRequest $Request
	 * @param \CPath\Render\HTML\Header\IHeaderWriter $Head the writer inst to use
	 * @return void
	 */
	function writeHeaders(IRequest $Request, IHeaderWriter $Head) {
		if($this->mResponse instanceof IHTMLSupportHeaders)
			$this->mResponse->writeHeaders($Request, $Head);
	}

	/**
	 * Render request as html and sends headers as necessary
	 * @param IRequest $Request the IRequest inst for this render which contains the request and remaining args
	 * @param \CPath\Render\HTML\Attribute\IAttributes $Attr optional attributes for the input field
	 * @param IRenderHTML $Parent
	 * @return void
	 */
	function renderHTML(IRequest $Request, IAttributes $Attr = null, IRenderHTML $Parent = null) {
		//$this->sendHeaders('text/html');

		$Response = $this->mResponse;
		if($Response instanceof IRenderHTML && $Response !== $Parent) {
			$Response->renderHTML($Request, $Attr, $Parent);

		} else {
//			echo RI::ni(), "<code>";
//			RI::ai(1);

			$Renderer = new HTMLKeyMapRenderer($Request);
			$this->mapKeys($Renderer);
//
//			RI::ai(-1);
//			echo RI::ni(), "</code>";

		}
	}

    /**
     * Sends headers if necessary, executes the request, and renders an IResponse as JSON
     * @param IRequest $Request the IRequest inst for this render which contains the request and remaining args
     * @return void
     */
    function renderJSON(IRequest $Request) {
        //$this->sendHeaders('application/json');

        $Response = $this->mResponse;
        if($Response instanceof IRenderJSON) {
            $Response->renderJSON($Request);
        } elseif($Response instanceof IKeyMap) {
            $Renderer = new JSONKeyMapRenderer($Request);
            $Response->mapKeys($Renderer);

        } else {
            $Renderer = new JSONKeyMapRenderer($Request);
            $this->mapKeys($Renderer);

        }
    }

    /**
     * Sends headers if necessary, executes the request, and renders an IResponse as XML
     * @param IRequest $Request the IRequest inst for this render which contains the request and remaining args
     * @param string $rootElementName Optional name of the root element
     * @param bool $declaration
     * @return void
     */
    function renderXML(IRequest $Request, $rootElementName = 'root', $declaration = false) {
        //$this->sendHeaders('application/xml');

        $Response = $this->mResponse;
        if($Response instanceof IRenderXML) {
            $Response->renderXML($Request, $rootElementName, $declaration);

        } elseif($Response instanceof IKeyMap) {
            $Renderer = new XMLKeyMapRenderer($Request, $rootElementName, true);
            $Response->mapKeys($Renderer);

        } else {
            $Renderer = new XMLKeyMapRenderer($Request, $rootElementName, true);
            $this->mapKeys($Renderer);
        }
    }

    /**
     * Sends headers if necessary, executes the request, and renders an IResponse as plain text
     * @param IRequest $Request the IRequest inst for this render which contains the request and remaining args
     * @param bool $sendHeaders
     * @return void
     */
    function renderText(IRequest $Request, $sendHeaders=false) {
        //$this->sendHeaders('text/plain');

        $Response = $this->mResponse;
        if($Response instanceof IRenderText) {
            $Response->renderText($Request);

        } else {
	        $Renderer = new TextKeyMapRenderer($Request);
	        $this->mapKeys($Renderer);
        }
    }
}

