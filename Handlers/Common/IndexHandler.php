<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/5/14
 * Time: 4:41 PM
 */
namespace CPath\Handlers\Common;

use CPath\Render\HTML\Attribute\IAttributes;
use CPath\Render\HTML\IContainerHTML;
use CPath\Request\IRequest;
use CPath\Handlers\HTML\AbstractHTMLHandler;
use CPath\Handlers\HTML\Templates\DefaultTemplate;
use CPath\Handlers\Response\ResponseRenderer;

class IndexHandler extends AbstractHTMLHandler
{
    public function __construct(IContainerHTML $Template=null) {
        parent::__construct($Template ?: new DefaultTemplate());
    }


    /**
     * Render request as html
     * @param IRequest $Request the IRequest instance for this render which contains the request and remaining args
     * @param IAttributes $Attr optional attributes for the input field
     * @return String|void always returns void
     */
    function renderHTML(IRequest $Request, IAttributes $Attr = null) {
        // TODO: Implement renderHTML() method.
    }
}