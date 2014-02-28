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
use CPath\Framework\Data\Map\Interfaces\IMappable;
use CPath\Framework\Data\Map\Types\CallbackMap;
use CPath\Framework\Render\Attribute\IAttributes;
use CPath\Framework\Render\Common\IRenderAll;
use CPath\Framework\Render\HTML\IRenderHTML;
use CPath\Framework\Render\Text\IRenderText;
use CPath\Framework\Render\XML\IRenderXML;
use CPath\Framework\Render\Util\RenderIndents as RI;
use CPath\Framework\Request\Interfaces\IRequest;
use CPath\Framework\Response\Interfaces\IResponse;
use CPath\Log;

final class ResponseUtil implements IRenderAll {
    private $mResponse;

    function __construct(IResponse $Response) {
        $this->mResponse = $Response;
    }

    function sendHeaders($mimeType=NULL) {
        $Response = $this->mResponse;
        if(Base::isCLI())
            return;
        if(headers_sent($file, $line)) {
            Log::e("IResponseHelper", "Warning: Headers already sent by {$file}:{$line}");
            return;
        }
        $msg = $Response->getMessage();
        //list($msg) = explode("\n", $msg);
        $msg = preg_replace('/[^\w -]/', '', $msg);
        header("HTTP/1.1 " . $Response->getStatusCode() . " " . $msg);
        if($mimeType !== NULL)
            header("Content-Type: $mimeType");
        header('Access-Control-Allow-Origin: *');
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
     * Render this handler
     * @param IRequest $Request the IRequest instance for this render
     * @return String|void always returns void
     */
    function render(IRequest $Request)
    {
        $this->renderHtml($Request);
    }

    /**
     * Sends headers if necessary, executes the request, and renders an IResponse as JSON
     * @param IRequest $Request the IRequest instance for this render which contains the request and remaining args
     * @return void
     */
    function renderJSON(IRequest $Request)
    {
        $Response = $this->mResponse;
        if($Response instanceof IRenderXML) {
            $Response->renderXML($Request);
        } else {
            echo '{';
            if($Response instanceof IMappable) {
                $Response->mapData(new CallbackMap(function($key, $data, $flags) {
                    if($flags ^ CallbackMap::IS_FIRST)
                        echo ',';
                    echo json_encode($key), ':', json_encode($data);
                }));
            } else {
                echo "'", IResponse::JSON_CODE, "':", $Response->getStatusCode();
                echo ",'", IResponse::JSON_MESSAGE, "':", json_encode($Response->getMessage());

                // TODO: Backwards compatability
                echo "'status':", $Response->getStatusCode() == IResponse::STATUS_SUCCESS ? 'true' : 'false';
                echo ",msg':", json_encode($Response->getMessage());
            }
            echo '}';
        }
    }

    /**
     * Sends headers if necessary, executes the request, and renders an IResponse as XML
     * @param IRequest $Request the IRequest instance for this render which contains the request and remaining args
     * @param string $rootElementName Optional name of the root element
     * @return void
     */
    function renderXML(IRequest $Request, $rootElementName = 'root')
    {
        $Response = $this->mResponse;
        if($Response instanceof IRenderXML) {
            $Response->renderXML($Request);
        } else {
            echo RI::ni(), "<", $rootElementName, ">";
            if($Response instanceof IMappable) {
                $Response->mapData(new CallbackMap(function($key, $data, $flags) {
                    echo json_encode($key), ':', json_encode($data);
                    echo RI::ni(), "<", $key, ">", htmlspecialchars($data), "</", $key, ">";
                }));
            } else {
                echo RI::ni(), "<", IResponse::JSON_CODE, ">", $Response->getStatusCode(), "</", IResponse::JSON_CODE, ">";
                echo RI::ni(), "<", IResponse::JSON_MESSAGE, ">", htmlspecialchars($Response->getMessage()), "</", IResponse::JSON_MESSAGE, ">";
            }
            echo RI::ni(), "</", $rootElementName, ">";
        }
    }

    /**
     * Render request as html and sends headers as necessary
     * @param IRequest $Request the IRequest instance for this render which contains the request and remaining args
     * @param IAttributes $Attr optional attributes for the input field
     * @return void
     */
    function renderHtml(IRequest $Request, IAttributes $Attr=null) {
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
     * @return void
     */
    function renderText(IRequest $Request) {
        $Response = $this->mResponse;
        if($Response instanceof IRenderText) {
            $Response->renderText($Request);
        } else {
            echo "Status:  ", $Response->getStatusCode(), "\n";
            echo "Message: ", $Response->getMessage(), "\n";
            //print_r($Response->getDataPath()); // TODO: lol!
        }
    }
}

