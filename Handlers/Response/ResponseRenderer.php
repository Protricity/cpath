<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/5/14
 * Time: 5:01 PM
 */
namespace CPath\Handlers\Response;

use CPath\Data\Map\IDataMap;
use CPath\Data\Map\IMappable;
use CPath\Render\HTML\Attribute\IAttributes;
use CPath\Render\HTML\HTMLResponseBody;
use CPath\Render\HTML\IContainerHTML;
use CPath\Render\HTML\IRenderHTML;
use CPath\Render\JSON\IRenderJSON;
use CPath\Render\JSON\JSONRenderMap;
use CPath\Render\Text\IRenderText;
use CPath\Render\XML\IRenderXML;
use CPath\Render\XML\XMLRenderMap;
use CPath\Request\IStaticRequestHandler;
use CPath\Request\IRequest;
use CPath\Framework\Response\Interfaces\IResponse;
use CPath\Handlers\HTML\Layouts\ThreeSectionLayout;
use CPath\Handlers\Wrapper\MimeTypeSwitchWrapper;

interface IResponseHandler {

    /**
     * Process the request and return a response
     * @param \CPath\Request\IRequest $Request
     * @return IResponse
     */
    function processResponse(IRequest $Request);
}

final class ResponseRenderer implements IRenderHTML, IRenderXML, IRenderJSON, IRenderText
{
    private $mTemplate;
    private $mResponse;
    public function __construct(IResponse $Response, IContainerHTML $Template=null) {
        $this->mTemplate = $Template;
        $this->mResponseHandler = $Response;
    }

    /**
     * Render request as html
     * @param \CPath\Handlers\Response\IRenderRequest|\CPath\Request\IRequest $Request the IRequest instance for this render which contains the request and remaining args
     * @param \CPath\Render\HTML\Attribute\IAttributes $Attr optional attributes for the input field
     * @return String|void always returns void
     */
    function renderHTML(IRenderRequest $Request, IAttributes $Attr = null) {
        $Response = $this->mResponse;
        $Util = new ResponseUtil($Response, $this->mTemplate);
        $Util->renderHTML($Request);
    }

    /**
     * Render request as JSON
     * @param \CPath\Request\IRequest $Request the IRequest instance for this render which contains the request and remaining args
     * @return String|void always returns void
     */
    function renderJSON(IRequest $Request) {
        // TODO: Implement renderJSON() method.
    }

    /**
     * Render request as plain text
     * @param \CPath\Request\IRequest $Request the IRequest instance for this render which contains the request and remaining args
     * @return String|void always returns void
     */
    function renderText(IRequest $Request)
    {
        // TODO: Implement renderText() method.
    }

    /**
     * Render request as xml
     * @param \CPath\Request\IRequest $Request the IRequest instance for this render which contains the request and remaining args
     * @param string $rootElementName Optional name of the root element
     * @return String|void always returns void
     */
    function renderXML(IRequest $Request, $rootElementName = 'root')
    {
        // TODO: Implement renderXML() method.
    }
}
