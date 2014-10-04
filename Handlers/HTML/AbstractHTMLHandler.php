<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/6/14
 * Time: 12:07 PM
 */
namespace CPath\Handlers\HTML;

use CPath\Handlers\Response\ResponseUtil;
use CPath\Render\Exceptions\MissingRenderModeException;
use CPath\Render\HTML\HTMLMimeType;
use CPath\Render\HTML\IHTMLTemplate;
use CPath\Render\HTML\IRenderHTML;
use CPath\Render\JSON\IRenderJSON;
use CPath\Render\JSON\JSONMimeType;
use CPath\Render\Text\IRenderText;
use CPath\Render\Text\TextMimeType;
use CPath\Render\XML\IRenderXML;
use CPath\Render\XML\XMLMimeType;
use CPath\Request\IRequest;
use CPath\Response\IResponse;
use CPath\Response\IResponse;
use CPath\Response\Response;
use CPath\Route\IRoute;

abstract class AbstractHTMLHandler implements IRoute, IRenderHTML, IResponse
{
    const TAB = '    ';
    const TAB_START = 0;

    const RESPONSE_CODE = 200;
    const RESPONSE_MESSAGE = 'OK';
    const RESPONSE_MIMETYPE = 'text/html';

    private $mTemplate = null;

    /**
     * Initialize handler with optional template container
     * @param IHTMLTemplate|null $Template optionally add an html container to wrap HTML render requests
     */
    public function __construct(IHTMLTemplate $Template = null) {
        $this->mTemplate = $Template;
    }

    /**
     * Render this request
     * @param IRequest $Request the IRequest instance for this render
     * @throws \CPath\Render\Exceptions\MissingRenderModeException
     * @return String|void always returns void
     */
    static function routeRequestStatic(IRequest $Request) {
        $MimeType = $Request->getMimeType();

        if($MimeType instanceof HTMLMimeType)
            $this->handleHTMLRequest($Request);

        elseif($MimeType instanceof JSONMimeType)
            $this->handleJSONRequest($Request);

        elseif($MimeType instanceof XMLMimeType)
            $this->handleXMLRequest($Request);

        elseif($MimeType instanceof TextMimeType)
            $this->handleTextRequest($Request);

        throw new MissingRenderModeException("Render mode could not be determined for [" . $Request->getMimeType() . "]: " . get_class($this));
    }

    protected function handleHTMLRequest(IRequest $Request) {
        $Util = new ResponseUtil($this);
        $Util->sendHeaders('text/html');

        if ($this->mTemplate) {
            $this->mTemplate->renderHTMLContent($Request, $this);
        } else {
            $this->renderHTML($Request);
        }
    }

    protected function handleJSONRequest(IRequest $Request) {
        if ($this instanceof IRenderJSON) {
            $Response = $this;
        } else {
            $Response = new Response("interface IRenderJSON not implemented for " . get_class($this), IResponse::HTTP_ERROR);
        }

        $Util = new ResponseUtil($Response);
        $Util->sendHeaders('application/json');
        $this->renderJSON($Request);
    }

    protected function handleXMLRequest(IRequest $Request) {
        if ($this instanceof IRenderXML) {
            $Response = $this;
        } else {
            $Response = new Response("interface IRenderXML not implemented for " . get_class($this), IResponse::HTTP_ERROR);
        }

        $Util = new ResponseUtil($Response);
        $Util->sendHeaders('application/xml');
        $this->renderXML($Request);
    }

    protected function handleTextRequest(IRequest $Request) {
        if ($this instanceof IRenderText) {
            $Response = $this;
        } else {
            $Response = new Response("interface IRenderText not implemented for " . get_class($this), IResponse::HTTP_ERROR);
        }

        $Util = new ResponseUtil($Response);
        $Util->sendHeaders('text/plain');
        $this->renderText($Request);
    }

    /**
     * Get the IResponse Message
     * @return String
     */
    function getMessage() {
        return static::RESPONSE_MESSAGE;
    }

    /**
     * Get the request status code
     * @return int
     */
    function getCode() {
        return static::RESPONSE_CODE;
    }
}