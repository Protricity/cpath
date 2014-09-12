<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 3/23/14
 * Time: 2:06 PM
 */
namespace CPath\Framework\Render\Common;

use CPath\Framework\Data\Map\Interfaces\IMappable;
use CPath\Render\HTML\Attribute\IAttributes;
use CPath\Framework\Render\IRenderAll;
use CPath\Render\HTML\HTMLMapUtil;
use CPath\Render\JSON\JSONMapUtil;
use CPath\Render\Text\TextRenderer;
use CPath\Render\XML\XMLRenderer;
use CPath\Request\IRequest;

class MapRenderer implements IRenderAll
{
    private $mMap;

    public function __construct(IMappable $Map)
    {
        $this->mMap = $Map;
    }

    /**
     * Render request as html and sends headers as necessary
     * @param \CPath\Request\IRequest $Request the IRequest instance for this render which contains the request and remaining args
     * @param \CPath\Render\HTML\Attribute\IAttributes $Attr optional attributes for the input field
     * @return String|void always returns void
     */
    function renderHTML(IRequest $Request, IAttributes $Attr = null)
    {
        HTMLMapUtil::renderMap($this->mMap);
    }

    /**
     * Render request as JSON
     * @param IRequest $Request the IRequest instance for this render which contains the request and remaining args
     * @return String|void always returns void
     */
    function renderJSON(IRequest $Request)
    {
        JSONMapUtil::renderMap($this->mMap);
    }

    /**
     * Render request as plain text
     * @param \CPath\Request\IRequest $Request the IRequest instance for this render which contains the request and remaining args
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
     * @param bool $declaration
     * @return String|void always returns void
     */
    function renderXML(IRequest $Request, $rootElementName = 'root', $declaration=true)
    {
        XMLRenderer::renderMap($this->mMap, $rootElementName, $declaration);
    }
}