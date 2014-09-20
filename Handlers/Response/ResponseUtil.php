<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Handlers\Response;
use CPath\Base;
use CPath\Config;
use CPath\Framework\Data\Map\Common\MappableCallback;
use CPath\Data\Map\IKeyMap;
use CPath\Data\Map\IMappableKeys;
use CPath\Render\HTML\Attribute\IAttributes;
use CPath\Render\HTML\HTMLContent;
use CPath\Render\HTML\HTMLElement;
use CPath\Render\HTML\IContainerHTML;
use CPath\Render\HTML\IRenderHTML;
use CPath\Framework\Render\IRenderAll;
use CPath\Render\JSON\IRenderJSON;
use CPath\Render\JSON\JSONRenderMap;
use CPath\Render\Text\IRenderText;
use CPath\Framework\Render\Util\RenderIndents as RI;
use CPath\Handlers\Wrapper\MimeTypeSwitchWrapper;
use CPath\Render\XML\IRenderXML;
use CPath\Render\XML\XMLRenderMap;
use CPath\Request\IRequest;
use CPath\Framework\Response\Interfaces\IResponse;

final class ResponseUtil implements IMappableKeys, IRenderHTML, IRenderXML, IRenderJSON, IRenderText {
    private $mResponse;
    //private $mContainer;

    function __construct(IResponse $Response) {
        $this->mResponse = $Response;
        //$this->mContainer = $HTMLContainer;
    }

    public function sendHeaders($mimeType = 'text/html') {
        $Response = $this->mResponse;

        if (headers_sent())
            throw new \Exception("Headers were already sent");

        header("HTTP/1.1 " . $Response->getCode() . " " . preg_replace('/[^\w -]/', '', $Response->getMessage()));
        if ($mimeType)
            header("Content-Type: " . $mimeType);

        header('Access-Control-Allow-Origin: *');
    }

    /**
     * Map data to a data map
     * @param IKeyMap $Map the map instance to add data to
     * @return void
     */
    function mapKeys(IKeyMap $Map) {
        $Response = $this->mResponse;

        $Map->map(IResponse::STR_MESSAGE, $Response->getMessage());
        $Map->map(IResponse::STR_CODE, $Response->getCode());
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
        } elseif($Response instanceof IMappableKeys) {
            $Renderer = new JSONRenderMap();
            $Response->mapKeys($Renderer);

        } else {
            $Renderer = new JSONRenderMap();
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
        } elseif($Response instanceof IMappableKeys) {
            $Renderer = new XMLRenderMap($rootElementName, true);
            $Response->mapKeys($Renderer);

        } else {
            $Renderer = new XMLRenderMap($rootElementName, true);
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
                new HTMLElement('label', null, 'Status: ' . $Response->getCode()),
                new HTMLElement('label', null, 'Message: ' . $Response->getMessage())
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
            echo RI::ni(), "Status:  ", $Response->getCode();
            echo RI::ni(), "Message: ", $Response->getMessage();
        }
    }
}

