<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Handlers\Response;
use CPath\Config;
use CPath\Data\Map\IKeyMap;
use CPath\Data\Map\IKeyMapper;
use CPath\Framework\Render\Util\RenderIndents as RI;
use CPath\Render\HTML\Attribute\IAttributes;
use CPath\Render\HTML\Element\HTMLElement;
use CPath\Render\HTML\IRenderHTML;
use CPath\Render\JSON\IRenderJSON;
use CPath\Render\JSON\JSONKeyMapRenderer;
use CPath\Render\Text\IRenderText;
use CPath\Render\XML\IRenderXML;
use CPath\Render\XML\XMLKeyMapRenderer;
use CPath\Request\IRequest;
use CPath\Response\IHeaderResponse;
use CPath\Response\IResponse;

final class ResponseUtil implements IKeyMap, IRenderHTML, IRenderXML, IRenderJSON, IRenderText, IHeaderResponse {
    private $mResponse;
    private $mSent = false;
    //private $mContainer;

    function __construct(IResponse $Response) {
        $this->mResponse = $Response;
        //$this->mContainer = $HTMLContainer;
    }

	/**
	 * Get the request status code
	 * @return int
	 */
	function getCode() {
		return $this->mResponse->getCode();
	}

	/**
	 * Get the IResponse Message
	 * @return String
	 */
	function getMessage() {
		return $this->mResponse->getMessage();
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
        header("Content-Type: " . $mimeType);

        header('Access-Control-Allow-Origin: *');

        $this->mSent = true;
        return true;
    }

	/**
	 * Map data to a data map
	 * @param IKeyMapper $Map the map instance to add data to
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
     * Sends headers if necessary, executes the request, and renders an IResponse as JSON
     * @param IRequest $Request the IRequest instance for this render which contains the request and remaining args
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
     * @param IRequest $Request the IRequest instance for this render which contains the request and remaining args
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
     * Render request as html and sends headers as necessary
     * @param IRequest $Request the IRequest instance for this render which contains the request and remaining args
     * @param \CPath\Render\HTML\Attribute\IAttributes $Attr optional attributes for the input field
     * @return void
     */
    function renderHTML(IRequest $Request, IAttributes $Attr=null) {
        //$this->sendHeaders('text/html');

        $Response = $this->mResponse;
        if($Response instanceof IRenderHTML) {
            $Render = $Response;
        } else {
            $Render = new HTMLElement('div', null,
                new HTMLElement('label', null, 'Status: ' . $this->getCode()),
                new HTMLElement('label', null, 'Message: ' . $this->getMessage())
            );
        }

        $Render->renderHTML($Request);
    }

    /**
     * Sends headers if necessary, executes the request, and renders an IResponse as plain text
     * @param IRequest $Request the IRequest instance for this render which contains the request and remaining args
     * @param bool $sendHeaders
     * @return void
     */
    function renderText(IRequest $Request, $sendHeaders=false) {
        //$this->sendHeaders('text/plain');

        $Response = $this->mResponse;
        if($Response instanceof IRenderText) {
            $Response->renderText($Request);
        } else {
            echo RI::ni(), "Status:  ", $this->getCode();
            echo RI::ni(), "Message: ", $this->getMessage();
        }
    }
}

