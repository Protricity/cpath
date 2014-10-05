<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/16/14
 * Time: 9:23 PM
 */
namespace CPath\Response;

use CPath\Data\Map\IKeyMap;
use CPath\Data\Map\ISequenceMap;
use CPath\Framework\Render\Header\IHeaderWriter;
use CPath\Framework\Render\Header\IHTMLSupportHeaders;
use CPath\Framework\Render\Util\RenderIndents as RI;
use CPath\Handlers\Response\ResponseUtil;
use CPath\Render\HTML\Attribute;
use CPath\Render\HTML\Attribute\IAttributes;
use CPath\Render\HTML\HTMLKeyMapRenderer;
use CPath\Render\HTML\HTMLMimeType;
use CPath\Render\HTML\HTMLResponseBody;
use CPath\Render\HTML\HTMLSequenceMapRenderer;
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
use CPath\Request\MimeType\UnknownMimeType;


class ResponseRenderer implements IHTMLSupportHeaders, IRenderHTML, IRenderText, IRenderJSON, IRenderXML // IRender,
{
    private $mResponse;
    public function __construct(IResponse $Response) {
	    $this->mResponse = $Response;
    }

	/**
	 * @return mixed
	 */
	public function getResponse() {
		return $this->mResponse;
	}

    /**
     * Renders a response object or returns false
     * @param IRequest $Request the IRequest instance for this render
     * @param bool $sendHeaders
     * @internal param \CPath\Request\Executable\IExecutable|\CPath\Response\IResponse $Response
     * @return bool returns false if no rendering occurred
     */
    function render(IRequest $Request, $sendHeaders=true) {
	    $Response = $this->getResponse();
        $MimeType = $Request->getMimeType();

        if($sendHeaders) {
            if($Response instanceof IHeaderResponse) {
                $Response->sendHeaders($Request, $MimeType->getName());
            } else {
                $Util = new ResponseUtil($Response);
                $Util->sendHeaders($Request, $MimeType->getName());
            }
        }

        if($MimeType instanceof HTMLMimeType) {
            $Template = $MimeType->getRenderContainer() ?: new HTMLResponseBody();
            $Template->renderHTMLContent($Request, $this);

        } elseif($MimeType instanceof XMLMimeType) {
            $this->renderXML($Request, $Response);

        } elseif($MimeType instanceof JSONMimeType) {
            $this->renderJSON($Request, $Response);

        } elseif($MimeType instanceof TextMimeType) {
            $this->renderText($Request, $Response);
        } elseif($MimeType instanceof UnknownMimeType) {
            //echo 'wut';
        }
    }

    /**
     * Write all support headers used by this IView instance
     * @param IRequest $Request
     * @param IHeaderWriter $Head the writer instance to use
     * @return String|void always returns void
     */
    function writeHeaders(IRequest $Request, IHeaderWriter $Head) {
        $Response = $this->getResponse();
        if($Response instanceof IHTMLSupportHeaders)
            $Response->writeHeaders($Request, $Head);
        if(!$Response instanceof IRenderHTML) {
            $HTMLMapRenderer = new HTMLSequenceMapRenderer($Request);
            $HTMLMapRenderer->writeHeaders($Request, $Head);
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
	    $Response = $this->getResponse();

        if ($Response instanceof IRenderHTML) {
            $Response->renderHTML($Request);

        } elseif ($Response instanceof IKeyMap) {
            $Renderer = new HTMLKeyMapRenderer($Request, $Attr);
            $Response->mapKeys($Request, $Renderer);

        } elseif ($Response instanceof ISequenceMap) {
            $Renderer = new HTMLSequenceMapRenderer($Request, $Attr);
            $Response->mapSequence($Request, $Renderer);

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
        $Response = $this->getResponse();

        if ($Response instanceof IRenderJSON) {
            $Response->renderJSON($Request);

        } elseif ($Response instanceof IKeyMap) {
            $Renderer = new JSONKeyMapRenderer($Request);
            $Response->mapKeys($Request, $Renderer);

        } elseif ($Response instanceof ISequenceMap) {
            $Renderer = new JSONSequenceMapRenderer($Request);
            $Response->mapSequence($Request, $Renderer);

        } else {
            $Map = new JSONKeyMapRenderer($Request);
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
	    $Response = $this->getResponse();

        if ($Response instanceof IRenderText) {
            $Response->renderText($Request);

        } elseif ($Response instanceof IKeyMap) {
            $Renderer = new TextKeyMapRenderer($Request);
            $Response->mapKeys($Request, $Renderer);

        } elseif ($Response instanceof ISequenceMap) {
            $Renderer = new TextSequenceMapRenderer($Request);
            $Response->mapSequence($Request, $Renderer);

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
	    $Response = $this->getResponse();

        if ($Response instanceof IRenderXML) {
            $Response->renderXML($Request, $declaration);

        } elseif ($Response instanceof IKeyMap) {
            $Renderer = new XMLKeyMapRenderer($Request, $rootElementName, $declaration);
            $Response->mapKeys($Request, $Renderer);

        } elseif ($Response instanceof ISequenceMap) {
            $Map = new XMLKeyMapRenderer($Request, $rootElementName, $declaration); // fill in 'root' for xml sequences?
            $Map->map('item', $Response);

        } else {
            $Map = new XMLKeyMapRenderer($Request, $rootElementName, $declaration);
            $Map->map(IResponse::STR_MESSAGE, $Response->getMessage());
            $Map->map(IResponse::STR_CODE, $Response->getCode());

        }
    }
}