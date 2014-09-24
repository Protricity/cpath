<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/16/14
 * Time: 9:23 PM
 */
namespace CPath\Response;

use CPath\Data\Map\IMappableKeys;
use CPath\Data\Map\IMappableSequence;
use CPath\Framework\Render\Util\RenderIndents as RI;
use CPath\Response\IResponse;
use CPath\Render\HTML\Attribute\IAttributes;
use CPath\Render\HTML\HTMLMimeType;
use CPath\Render\HTML\HTMLKeyMapRenderer;
use CPath\Render\HTML\HTMLResponseBody;
use CPath\Render\HTML\HTMLSequenceMapRenderer;
use CPath\Render\HTML\IContainerHTML;
use CPath\Render\HTML\IRenderHTML;
use CPath\Render\JSON\IRenderJSON;
use CPath\Render\JSON\JSONKeyMapRenderer;
use CPath\Render\JSON\JSONMimeType;
use CPath\Render\JSON\JSONSequenceMapRenderer;
use CPath\Render\Text\IRenderText;
use CPath\Render\Text\TextKeyMapRenderer;
use CPath\Render\Text\TextMimeType;
use CPath\Render\Text\TextSequenceMapRenderer;
use CPath\Render\XML\IRenderXML;
use CPath\Render\XML\XMLKeyMapRenderer;
use CPath\Render\XML\XMLMimeType;
use CPath\Request\IRequest;

class ResponseRenderer implements IRenderHTML, IRenderXML, IRenderJSON, IRenderText
{
    private $mResponse;
    private $mHTMLRender;

    public function __construct(IResponse $Response, IContainerHTML $HTMLTemplate = null) {
        $this->mResponse = $Response;
        $this->mHTMLRender = $HTMLTemplate ?: new HTMLResponseBody();
        $this->mHTMLRender->addContent($this);
    }

    function render(IRequest $Request, $sendHeaders=true) {
        $Response = $this->mResponse;
        $MimeType = $Request->getMimeType();

        if($sendHeaders)
            $MimeType->sendHeaders($Response->getCode(), $Response->getMessage());

        if($MimeType instanceof HTMLMimeType) {
            $this->mHTMLRender->renderHTML($Request);

        } elseif($MimeType instanceof XMLMimeType) {
            $this->renderXML($Request);

        } elseif($MimeType instanceof JSONMimeType) {
            $this->renderJSON($Request);

        } elseif($MimeType instanceof TextMimeType) {
            $this->renderText($Request);
        }
    }

    /**
     * Render request as html
     * @param \CPath\Request\IRequest $Request the IRequest instance for this render which contains the request and remaining args
     * @param \CPath\Render\HTML\Attribute\IAttributes $Attr
     * @internal param $ \CPath\Render\Attribute\\CPath\Render\HTML\Attribute\IAttributes $Attr optional attributes for the input field
     * @return String|void always returns void
     */
    function renderHTML(IRequest $Request, IAttributes $Attr = null) {
        $Response = $this->mResponse;

        if ($Response instanceof IRenderHTML) {
            $Response->renderHTML($Request);

        } elseif ($Response instanceof IMappableKeys) {
            $Renderer = new HTMLKeyMapRenderer($Request, $Attr);
            $Response->mapKeys($Renderer);

        } elseif ($Response instanceof IMappableSequence) {
            $Renderer = new HTMLSequenceMapRenderer($Request, $Attr);
            $Response->mapSequence($Renderer);

        } else {
            $Renderer = new HTMLKeyMapRenderer($Request, $Attr);
            $Renderer->map(IResponse::STR_MESSAGE, $Response->getMessage());
            $Renderer->map(IResponse::STR_CODE, $Response->getCode());

        }
    }

    /**
     * Render request as JSON
     * @param \CPath\Request\IRequest $Request the IRequest instance for this render which contains the request and remaining args
     * @return String|void always returns void
     */
    function renderJSON(IRequest $Request) {
        $Response = $this->mResponse;

        if ($Response instanceof IRenderJSON) {
            $Response->renderJSON($Request);

        } elseif ($Response instanceof IMappableKeys) {
            $Renderer = new JSONKeyMapRenderer();
            $Response->mapKeys($Renderer);

        } elseif ($Response instanceof IMappableSequence) {
            $Renderer = new JSONSequenceMapRenderer();
            $Response->mapSequence($Renderer);

        } else {
            $Map = new JSONKeyMapRenderer();
            $Map->map(IResponse::STR_MESSAGE, $Response->getMessage());
            $Map->map(IResponse::STR_CODE, $Response->getCode());

        }
    }

    /**
     * Render request as plain text
     * @param IRequest $Request the IRequest instance for this render which contains the request and remaining args
     * @return String|void always returns void
     */
    function renderText(IRequest $Request) {
        $Response = $this->mResponse;

        if ($Response instanceof IRenderText) {
            $Response->renderText($Request);

        } elseif ($Response instanceof IMappableKeys) {
            $Renderer = new TextKeyMapRenderer();
            $Response->mapKeys($Renderer);

        } elseif ($Response instanceof IMappableSequence) {
            $Renderer = new TextSequenceMapRenderer();
            $Response->mapSequence($Renderer);

        } else {
            echo RI::ni(), "Status:  ", $Response->getCode();
            echo RI::ni(), "Message: ", $Response->getMessage();

        }
    }

    /**
     * Render request as xml
     * @param \CPath\Request\IRequest $Request the IRequest instance for this render which contains the request and remaining args
     * @param string $rootElementName Optional name of the root element
     * @param bool $declaration
     * @return String|void always returns void
     */
    function renderXML(IRequest $Request, $rootElementName = 'root', $declaration = false) {
        $Response = $this->mResponse;

        if ($Response instanceof IRenderXML) {
            $Response->renderXML($Request, $declaration);

        } elseif ($Response instanceof IMappableKeys) {
            $Renderer = new XMLKeyMapRenderer($rootElementName, $declaration);
            $Response->mapKeys($Renderer);

        } elseif ($Response instanceof IMappableSequence) {
            $Map = new XMLKeyMapRenderer($rootElementName, $declaration); // fill in 'root' for xml sequences?
            $Map->map('item', $Response);

        } else {
            $Map = new XMLKeyMapRenderer($rootElementName, $declaration);
            $Map->map(IResponse::STR_MESSAGE, $Response->getMessage());
            $Map->map(IResponse::STR_CODE, $Response->getCode());

        }
    }
}