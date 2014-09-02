<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Framework\Render\Util;

use CPath\Framework\Render\Attribute\IAttributes;
use CPath\Framework\Render\Exceptions\MissingRenderModeException;
use CPath\Framework\Render\HTML\IRenderHTML;
use CPath\Framework\Render\IRender;
use CPath\Framework\Render\IRenderAll;
use CPath\Framework\Render\JSON\IRenderJSON;
use CPath\Framework\Render\Text\IRenderText;
use CPath\Framework\Render\XML\IRenderXML;
use CPath\Framework\Request\Interfaces\IRequest;
use CPath\Framework\Response\Types\ExceptionResponse;
use CPath\Framework\Response\Util\ResponseUtil;

class RenderMimeSwitchUtility implements IRenderAll, IRender {
    private $mTarget;

    /**
     * @param mixed $RenderTarget
     */
    function __construct($RenderTarget) {
        $this->mTarget = $RenderTarget;
    }

    function getRenderTarget() {
        return $this->mTarget;
    }

    function renderOrThrow(IRequest $Request) {
        $this->render($Request);
    }

    /**
     * Render this request
     * @param IRequest $Request the IRequest instance for this render
     * @throws MissingRenderModeException
     * @return String|void always returns void
     */
    function render(IRequest $Request) {
        foreach($Request->getMimeTypes() as $mimeType) {
            switch($mimeType) {
                case 'application/json':
                    $this->renderJSON($Request);
                    return;

                case 'application/xml':
                    $this->renderXML($Request);
                    return;

                case 'text/html':
                    $this->renderHTML($Request);
                    return;

                case 'text/plain':
                    $this->renderText($Request);
                    return;
            }
        }

        $types = implode(', ', $Request->getMimeTypes());
        throw new MissingRenderModeException("Render mode could not be determined for [{$types}]: " . get_class($this->getRenderTarget()));
    }

    /**
     * Render request as html and sends headers as necessary
     * @param IRequest $Request the IRequest instance for this render which contains the request and remaining args
     * @param IAttributes $Attr optional attributes for the input field
     * @return void
     */
    function renderHTML(IRequest $Request, IAttributes $Attr = null)
    {
        if(!headers_sent())
            header("Content-Type: text/html");
        $Target = $this->mTarget;
        try {
            if($Target instanceof IRenderHTML) {
                $Target->renderHTML($Request);
                return;
            }
            $types = implode(', ', $Request->getMimeTypes());
            throw new MissingRenderModeException("Render mode could not be determined for [{$types}]: " . get_class($this->getRenderTarget()));

        } catch (\Exception $ex) {
            $ErrorResponse = new ExceptionResponse($ex);
            $Util = new ResponseUtil($ErrorResponse);
            $Util->renderHTML($Request, null, true);
        }
    }

    /**
     * Render request as JSON
     * @param IRequest $Request the IRequest instance for this render which contains the request and remaining args
     * @return void
     */
    function renderJSON(IRequest $Request)
    {
        if(!headers_sent())
            header("Content-Type: application/json");
        $Target = $this->mTarget;
        try {
            if($Target instanceof IRenderJSON) {
                $Target->renderJSON($Request);
                return;
            }
            $types = implode(', ', $Request->getMimeTypes());
            throw new MissingRenderModeException("Render mode could not be determined for [{$types}]: " . get_class($this->getRenderTarget()));

        } catch (\Exception $ex) {
            $ErrorResponse = new ExceptionResponse($ex);
            $Util = new ResponseUtil($ErrorResponse);
            $Util->renderJSON($Request, true);
        }
    }

    /**
     * Render request as plain text
     * @param IRequest $Request the IRequest instance for this render which contains the request and remaining args
     * @return void
     */
    function renderText(IRequest $Request)
    {
        if(!headers_sent())
            header("Content-Type: text/plain");
        $Target = $this->mTarget;
        try {
            if($Target instanceof IRenderText) {
                $Target->renderText($Request);
                return;
            }
            $types = implode(', ', $Request->getMimeTypes());
            throw new MissingRenderModeException("Render mode could not be determined for [{$types}]: " . get_class($this->getRenderTarget()));

        } catch (\Exception $ex) {
            $ErrorResponse = new ExceptionResponse($ex);
            $Util = new ResponseUtil($ErrorResponse);
            $Util->renderText($Request);
        }
    }

    /**
     * Render request as xml
     * @param IRequest $Request the IRequest instance for this render which contains the request and remaining args
     * @param string $rootElementName Optional name of the root element
     * @return void
     */
    function renderXML(IRequest $Request, $rootElementName = 'root')
    {
        if(!headers_sent())
            header("Content-Type: application/xml");
        $Target = $this->mTarget;
        try {
            if($Target instanceof IRenderXML) {
                $Target->renderXML($Request, $rootElementName);
                return;
            }
            $types = implode(', ', $Request->getMimeTypes());
            throw new MissingRenderModeException("Render mode could not be determined for [{$types}]: " . get_class($this->getRenderTarget()));

        } catch (\Exception $ex) {
            $ErrorResponse = new ExceptionResponse($ex);
            $Util = new ResponseUtil($ErrorResponse);
            $Util->renderXML($Request, $rootElementName);
        }
    }
}