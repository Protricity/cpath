<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/5/14
 * Time: 3:08 PM
 */
namespace CPath\Templates\HTML\Pages;

use CPath\Render\HTML\Attribute\IAttributes;
use CPath\Render\HTML\HTMLResponseBody;
use CPath\Render\HTML\IContainerHTML;
use CPath\Render\HTML\IRenderHTML;
use CPath\Request\IRequest;
use CPath\Handlers\HTML\Layouts\ThreeSectionLayout;
use CPath\Handlers\HTML\Navigation\OrderedListNavigator;

class IndexPage implements IContainerHTML
{
    /** @var IContainerHTML */
    private $mContent;
    /** @var IContainerHTML */
    private $mLayout;
    private $mNavBar;

    public function __construct() {
        $this->mContent = new HTMLResponseBody(
            $this->mLayout = new ThreeSectionLayout(
            )
        );
    }

    /**
     * Add HTML Container Content
     * @param IRenderHTML $Content
     * @return String|void always returns void
     */
    function addContent(IRenderHTML $Content)
    {
        $this->mLayout->addContent($Content);
    }

    /**
     * Render request as html
     * @param \CPath\Request\IRequest|\CPath\Templates\HTML\Pages\IRenderRequest $Request the IRequest instance for this render which contains the request and remaining args
     * @param \CPath\Render\HTML\Attribute\IAttributes $Attr optional attributes for the input field
     * @return String|void always returns void
     */
    function renderHTML(IRenderRequest $Request, IAttributes $Attr = null)
    {
        $this->mContent->renderHTML($Request, $Attr);
    }
}

