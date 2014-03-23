<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 3/23/14
 * Time: 2:06 PM
 */
namespace CPath\Framework\Render\Common;

use CPath\Framework\Data\Map\Interfaces\IMappable;
use CPath\Framework\Render\Attribute\IAttributes;
use CPath\Framework\Render\IRender;
use CPath\Framework\Render\JSON\Renderers\HTMLRenderer;
use CPath\Framework\Render\JSON\Renderers\JSONRenderer;
use CPath\Framework\Render\JSON\Renderers\TextRenderer;
use CPath\Framework\Render\XML\Renderers\XMLRenderer;
use CPath\Framework\Request\Interfaces\IRequest;

class MapRenderer implements IRender
{
    private $mMap;

    public function __construct(IMappable $Map)
    {
        $this->mMap = $Map;
    }

    /**
     * Render request as html and sends headers as necessary
     * @param IRequest $Request the IRequest instance for this render which contains the request and remaining args
     * @param IAttributes $Attr optional attributes for the input field
     * @return String|void always returns void
     */
    function renderHtml(IRequest $Request, IAttributes $Attr = null)
    {
        HTMLRenderer::renderMap($this->mMap);
    }

    /**
     * Render request as JSON
     * @param IRequest $Request the IRequest instance for this render which contains the request and remaining args
     * @return String|void always returns void
     */
    function renderJSON(IRequest $Request)
    {
        JSONRenderer::renderMap($this->mMap);
    }

    /**
     * Render request as plain text
     * @param IRequest $Request the IRequest instance for this render which contains the request and remaining args
     * @return String|void always returns void
     */
    function renderText(IRequest $Request)
    {
        TextRenderer::renderMap($this->mMap);
    }

    /**
     * Render request as xml
     * @param IRequest $Request the IRequest instance for this render which contains the request and remaining args
     * @param string $rootElementName Optional name of the root element
     * @return String|void always returns void
     */
    function renderXML(IRequest $Request, $rootElementName = 'root')
    {
        XMLRenderer::renderMap($this->mMap, $rootElementName);
    }
}