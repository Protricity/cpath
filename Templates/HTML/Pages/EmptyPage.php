<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/5/14
 * Time: 3:52 PM
 */
namespace CPath\Templates\HTML\Pages;

use CPath\Render\HTML\Attribute\IAttributes;
use CPath\Render\HTML\HTMLResponseBody;
use CPath\Render\HTML\IContainerHTML;
use CPath\Render\HTML\IRenderHTML;
use CPath\Request\IRequest;

class EmptyPage implements IContainerHTML
{
    /** @var IContainerHTML */
    private $mContent;

    public function __construct() {
        $this->mContent = new HTMLResponseBody();
    }

    /**
     * Add HTML Container Content
     * @param IRenderHTML $Content
     * @return String|void always returns void
     */
    function addContent(IRenderHTML $Content) {
        $this->mContent->addContent($Content);
    }

    /**
     * Render request as html
     * @param \CPath\Request\IRequest|\CPath\Templates\HTML\Pages\IRequest $Request the IRequest instance for this render which contains the request and remaining args
     * @param \CPath\Render\HTML\Attribute\IAttributes $Attr
     * @internal param $ \CPath\Render\Attribute\\CPath\Render\HTML\Attribute\IAttributes $Attr optional attributes for the input field
     * @return String|void always returns void
     */
    function renderHTML(IRequest $Request, IAttributes $Attr = null) {
        $this->mContent->renderHTML($Request, $Attr);
    }
}