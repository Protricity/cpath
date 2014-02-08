<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Framework\Api\Util;

use CPath\Base;
use CPath\Framework\Api\Exceptions\FieldNotFoundException;
use CPath\Framework\Api\Interfaces\IAPI;
use CPath\Framework\Api\Interfaces\IField;
use CPath\Framework\Render\Interfaces\IRenderHtml;
use CPath\Framework\Render\Interfaces\IRenderJSON;
use CPath\Framework\Render\Interfaces\IRenderText;
use CPath\Framework\Render\Interfaces\IRenderXML;
use CPath\Framework\Request\Interfaces\IRequest;
use CPath\Handlers\Views\APIView;
use CPath\Framework\Render\Interfaces\IRender;
use CPath\Framework\Response\Types\ExceptionResponse;
use CPath\Framework\Response\Interfaces\IResponse;
use CPath\Util;

class APIRenderUtil implements IAPI, IRender {
    private $mAPI;

    function __construct(IAPI $API) {
        $this->mAPI = $API;
    }

    /**
     * @return IAPI
     */
    public function getAPI() {
        return $this->mAPI;
    }

    /**
     * Execute this API Endpoint with the entire request returning an IResponse object
     * @param IRequest $Request the IRequest instance for this render which contains the request and args
     * @return \CPath\Framework\Response\\CPath\Framework\Response\Interfaces\IResponse the api call response with data, message, and status
     */
    final public function execute(IRequest $Request) {
        $Util = new APIExecuteUtil($this->getAPI());
        return $Util->execute($Request);
    }

    /**
     * Get all API Fields
     * @return IField[]
     */
    function getFields() {
        return $this->mAPI->getFields();
    }

    /**
     * Get an API field by name
     * @param String $fieldName the field name
     * @return IField
     * @throws FieldNotFoundException if the field was not found
     */
    public function getField($fieldName) {
        $Util = new APIExecuteUtil($this->getAPI());
        return $Util->getField($fieldName);
    }

    /**
     * Render this API Call. The output format is based on the requested mimeType from the browser
     * @param IRequest $Request the IRequest instance for this render which contains the request and remaining args
     * @return void
     */
    function render(IRequest $Request) {
        foreach($Request->getMimeTypes() as $mimeType) {
            switch($mimeType) {
                case 'application/json':
                    if($this->mAPI instanceof IRenderJSON)
                        $this->mAPI->renderJSON($Request);
                    else
                        $this->renderJSON($Request);
                    return;

                case 'application/xml':
                    if($this->mAPI instanceof IRenderXML)
                        $this->mAPI->renderXML($Request);
                    else
                        $this->renderXML($Request);
                    return;

                case 'text/html':
                    if($this->mAPI instanceof IRenderHtml)
                        $this->mAPI->renderHTML($Request);
                    else
                        $this->renderHTML($Request);
                    return;

                case 'text/plain':
                    if($this->mAPI instanceof IRenderText)
                        $this->mAPI->renderText($Request);
                    else
                        $this->renderText($Request);
                    return;
            }
        }
        $this->renderDefault($Request);
    }


    /**
     * Sends headers, executes the request, and renders an IResponse as HTML
     * @param IRequest $Request the IRequest instance for this render which contains the request and remaining args
     * @return void
     */
    public function renderHTML(IRequest $Request) {
        if(!headers_sent() && !Base::isCLI())
            header("Content-Type: text/html");
        $Response = null;

        $Route = $Request->getRoute();
        $Render = new APIView($this, $Route, $Response);
        $Render->render($Request);
    }

    /**
     * Sends headers, executes the request, and renders an IResponse as JSON
     * @param IRequest $Request the IRequest instance for this render which contains the request and remaining args
     * @return void
     */
    public function renderJSON(IRequest $Request) {
        if(!headers_sent()) // && !Base::isCLI())
            header("Content-Type: application/json");
        $Response = $this->execute($Request);
        $Response->sendHeaders();
        try{
            $JSON = Util::toJSON($Response);
            echo json_encode($JSON);
        } catch (\Exception $ex) {
            $Response = new ExceptionResponse($ex);
            $JSON = Util::toJSON($Response);
            echo json_encode($JSON);
        }
    }

    /**
     * Sends headers, executes the request, and renders an IResponse as XML
     * @param IRequest $Request the IRequest instance for this render which contains the request and remaining args
     * @return void
     */
    public function renderXML(IRequest $Request) {
        if(!headers_sent()) // && !Base::isCLI())
            header("Content-Type: text/xml");
        $Response = $this->execute($Request);
        $Response->sendHeaders();
        try{
            $XML = Util::toXML($Response);
            echo $XML->asXML();
        } catch (\Exception $ex) {
            $Response = new ExceptionResponse($ex);
            $XML = Util::toXML($Response);
            echo $XML->asXML();
        }
    }

    /**
     * Sends headers, executes the request, and renders an IResponse as Plain Text
     * @param IRequest $Request the IRequest instance for this render which contains the request and remaining args
     * @return void
     */
    public function renderText(IRequest $Request) {
        $Response = $this->execute($Request);
        $Response->sendHeaders('text/plain');
        $Response->renderText();
    }

    /**
     * Renders via default method
     * @param IRequest $Request the IRequest instance for this render which contains the request and remaining args
     * @return void
     */
    public function renderDefault(IRequest $Request) {
        $this->renderText($Request);
    }
}