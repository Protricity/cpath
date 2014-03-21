<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Framework\Api\Util;

use CPath\Describable\Describable;
use CPath\Describable\IDescribable;
use CPath\Describable\IDescribableAggregate;
use CPath\Framework\Api\Exceptions\FieldNotFoundException;
use CPath\Framework\Api\Field\Interfaces\IField;
use CPath\Framework\Api\Interfaces\IAPI;
use CPath\Framework\Render\Attribute\IAttributes;
use CPath\Framework\Render\IRenderAll;
use CPath\Framework\Request\Interfaces\IRequest;
use CPath\Framework\Response\Interfaces\IResponse;
use CPath\Framework\Response\Util\ResponseUtil;
use CPath\Framework\Route\Render\IDestination;

class APIRenderUtil implements IAPI, IRenderAll, IDestination, IDescribableAggregate {
    private $mAPI;

    function __construct(IAPI $API) {
        $this->mAPI = $API;
    }

    /**
     * Get the Object Description
     * @return IDescribable|String a describable Object, or string describing this object
     */
    function getDescribable()
    {
        return Describable::get($this->mAPI);
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
     * @return IResponse the api call response with data, message, and status
     */
    final public function execute(IRequest $Request, $args) {
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
     * Render this route destination
     * @param IRequest $Request the IRequest instance for this render
     * @param String $path the matched request path for this destination
     * @param String[] $args the arguments appended to the path
     * @return String|void always returns void
     */
    function renderDestination(IRequest $Request, $path, $args)
    {
        // TODO: Implement renderDestination() method.
        $this->render($Request);
    }

    /**
     * Render this API Call. The output format is based on the requested mimeType from the browser
     * @param IRequest $Request the IRequest instance for this render which contains the request and remaining args
     * @return void
     */
    function render(IRequest $Request) {
        $Response = $this->execute($Request);
        $Util = new ResponseUtil($Response);
        $Util->renderDestination($Request);
    }


    /**
     * Render request as html and sends headers as necessary
     * @param IRequest $Request the IRequest instance for this render which contains the request and remaining args
     * @param IAttributes $Attr optional attributes for the input field
     * @return void
     */
    function renderHtml(IRequest $Request, IAttributes $Attr=null) {
        $Response = $this->execute($Request);
        $Util = new ResponseUtil($Response);
        $Util->renderHtml($Request, $Attr);
    }

    /**
     * Sends headers, executes the request, and renders an IResponse as JSON
     * @param IRequest $Request the IRequest instance for this render which contains the request and remaining args
     * @return void
     */
    public function renderJSON(IRequest $Request) {
//        if(!headers_sent()) // && !Base::isCLI())
//            header("Content-Type: application/json");

        $Response = $this->execute($Request);
        $Util = new ResponseUtil($Response);
        $Util->renderJSON($Request);
    }

    /**
     * Sends headers, executes the request, and renders an IResponse as XML
     * @param IRequest $Request the IRequest instance for this render which contains the request and remaining args
     * @param string $rootElementName
     * @return void
     */
    public function renderXML(IRequest $Request, $rootElementName='root') {
        $Response = $this->execute($Request);
        $Util = new ResponseUtil($Response);
        $Util->renderXML($Request);

//        if(!headers_sent()) // && !Base::isCLI())
////            header("Content-Type: text/xml");
//        $Response = $this->execute($Request);
//        $Response->sendHeaders();
//        try{
//            $XML = Util::toXML($Response);
//            echo $XML->asXML();
//        } catch (\Exception $ex) {
//            $Response = new ExceptionResponse($ex);
//            $XML = Util::toXML($Response);
//            echo $XML->asXML();
//        }
    }

    /**
     * Sends headers, executes the request, and renders an IResponse as Plain Text
     * @param IRequest $Request the IRequest instance for this render which contains the request and remaining args
     * @return void
     */
    public function renderText(IRequest $Request) {
        $Response = $this->execute($Request);
        $Util = new ResponseUtil($Response);
        $Util->renderText($Request);
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