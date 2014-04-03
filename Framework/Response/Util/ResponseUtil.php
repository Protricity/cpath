<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Framework\Response\Util;
use CPath\Base;
use CPath\Config;
use CPath\Framework\Data\Map\Common\MappableCallback;
use CPath\Framework\Data\Map\Interfaces\IDataMap;
use CPath\Framework\Data\Map\Interfaces\IMappable;
use CPath\Framework\Render\Attribute\IAttributes;
use CPath\Framework\Render\HTML\IRenderHTML;
use CPath\Framework\Render\IRenderAll;
use CPath\Framework\Render\JSON\IRenderJSON;
use CPath\Framework\Render\JSON\Renderers\JSONRenderer;
use CPath\Framework\Render\Text\IRenderText;
use CPath\Framework\Render\Util\RenderIndents as RI;
use CPath\Framework\Render\Util\RenderMimeSwitchUtility;
use CPath\Framework\Render\XML\IRenderXML;
use CPath\Framework\Render\XML\Renderers\XMLRenderer;
use CPath\Framework\Request\Interfaces\IRequest;
use CPath\Framework\Response\Interfaces\IResponse;

final class ResponseUtil implements IMappable, IRenderAll {
    private $mResponse;

    private static $mSent = false;

    function __construct(IResponse $Response) {
        $this->mResponse = $Response;
    }

    /**
     * Map data to a data map
     * @param IDataMap $Map the map instance to add data to
     * @param mixed $dataObject
     * @return void
     */
    function mapData(IDataMap $Map, $dataObject=null)
    {
        $Response = $this->mResponse;

        $Map->mapKeyValue(IResponse::JSON_MESSAGE, $Response->getMessage());
        $Map->mapKeyValue(IResponse::JSON_CODE, $Response->getCode());

        // TODO: remove Backwards compatability
        $Map->mapKeyValue('msg', $Response->getMessage());
        $Map->mapKeyValue('status', $Response->getCode() == IResponse::STATUS_SUCCESS ? 'true' : 'false');

        if($dataObject !== null) {
            if($dataObject instanceof IMappable)
                $Map->mapSubsection(IResponse::JSON_RESPONSE, new MappableCallback( function(IDataMap $Map) use ($dataObject) {
                    $dataObject->mapData($Map);
                }));
            else
                $Map->mapKeyValue(IResponse::JSON_RESPONSE, $dataObject);
        }
    }

    /**
     * Send headers associated with this response
     * @param null $mimeType
     * @return bool true if headers were sent, false otherwise
     */
    function sendHeaders($mimeType=NULL) {
        if(self::$mSent || Base::isCLI() || headers_sent())
            return false;

        $Response = $this->mResponse;
        $msg = preg_replace('/[^\w -]/', '', $Response->getMessage());
        $code = $Response->getCode();
        if(!$code || !is_numeric($code))
            throw new \InvalidArgumentException("Invalid Status Code: {$code}");

        header("HTTP/1.1 " . $code . " " . $msg);
        if($mimeType !== NULL)
            header("Content-Type: $mimeType");
        header('Access-Control-Allow-Origin: *');
        self::$mSent = true;
        return true;
    }

//    function toString() {
//        $Response = $this->mResponse;
//        return
//            $Response->getStatusCode() . " " . $Response->getMessage()
//            . (Config::$Debug ? "\n" . print_r($Response->getDataPath() ?: NULL, true) : ''); // TODO: IText
//    }

    // Static

//    static function get(\CPath\Framework\Response\Interfaces\IResponse $Response) {
//        return new static($Response);
//    }

    /**
     * Render this request
     * @param IRequest $Request the IRequest instance for this render
     * @return String|void always returns void
     */
    function render(IRequest $Request)
    {
        $Util = new RenderMimeSwitchUtility($this);
        $Util->render($Request);
    }

    /**
     * Sends headers if necessary, executes the request, and renders an IResponse as JSON
     * @param IRequest $Request the IRequest instance for this render which contains the request and remaining args
     * @param bool $sendHeaders
     * @return void
     */
    function renderJSON(IRequest $Request, $sendHeaders=false)
    {
        if($sendHeaders)
            $this->sendHeaders('application/json');
        $Response = $this->mResponse;
        if($Response instanceof IRenderJSON) {
            $Response->renderJSON($Request);
        } elseif($Response instanceof IMappable) {
            JSONRenderer::renderMap($Response);
        } else {
            JSONRenderer::renderMap($this);
        }
    }

    /**
     * Sends headers if necessary, executes the request, and renders an IResponse as XML
     * @param IRequest $Request the IRequest instance for this render which contains the request and remaining args
     * @param string $rootElementName Optional name of the root element
     * @param bool $sendHeaders
     * @return void
     */
    function renderXML(IRequest $Request, $rootElementName = 'root', $sendHeaders=false)
    {
        if($sendHeaders)
            $this->sendHeaders('application/xml');
        $Response = $this->mResponse;
        if($Response instanceof IRenderXML) {
            $Response->renderXML($Request, $rootElementName);
        } elseif($Response instanceof IMappable) {
            XMLRenderer::renderMap($Response, $rootElementName, true);
        } else {
            XMLRenderer::renderMap($this, $rootElementName, true);
        }
    }

    /**
     * Render request as html and sends headers as necessary
     * @param IRequest $Request the IRequest instance for this render which contains the request and remaining args
     * @param IAttributes $Attr optional attributes for the input field
     * @param bool $sendHeaders
     * @return void
     */
    function renderHtml(IRequest $Request, IAttributes $Attr=null, $sendHeaders=false) {
        if($sendHeaders)
            $this->sendHeaders('text/html');
        $Response = $this->mResponse;
        if($Response instanceof IRenderHTML) {
            $Response->renderHtml($Request);
        } else {
            echo RI::ni(), "<pre>";
            RI::ni(1);
            $this->renderText($Request);
            RI::ni(2);
            echo RI::ni(), "</pre>";
        }
    }

    /**
     * Sends headers if necessary, executes the request, and renders an IResponse as plain text
     * @param IRequest $Request the IRequest instance for this render which contains the request and remaining args
     * @param bool $sendHeaders
     * @return void
     */
    function renderText(IRequest $Request, $sendHeaders=false) {
        if($sendHeaders)
            $this->sendHeaders('text/plain');

        $Response = $this->mResponse;
        if($Response instanceof IRenderText) {
            $Response->renderText($Request);
        } else {
            echo "Status:  ", $Response->getCode(), "\n";
            echo "Message: ", $Response->getMessage(), "\n";
            //print_r($Response->getDataPath()); // TODO: lol!
        }
    }
}

