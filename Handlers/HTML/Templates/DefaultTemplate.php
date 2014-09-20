<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/6/14
 * Time: 12:27 PM
 */
namespace CPath\Handlers\HTML\Templates;

use CPath\Render\HTML\Attribute\IAttributes;
use CPath\Render\HTML\HTMLHeaderStyleSheet;
use CPath\Render\HTML\HTMLResponseBody;
use CPath\Render\HTML\IContainerHTML;
use CPath\Render\HTML\IRenderHTML;
use CPath\Request\IRequest;
use CPath\Handlers\HTML\Layouts\ThreeSectionLayout;
use CPath\Handlers\HTML\Navigation\AbstractNavigator;
use CPath\Handlers\HTML\Navigation\OrderedListNavigator;

class DefaultTemplate implements IContainerHTML
{
    /** @var IContainerHTML */
    private $mResponseBody;
    /** @var IContainerHTML */
    private $mLayout;
    /** @var AbstractNavigator */
    private $mNavigation;

    public function __construct() {
        $this->mResponseBody = new HTMLResponseBody(
            new HTMLHeaderStyleSheet(__NAMESPACE__ . '\assets\default-template.css'),

            $this->mLayout = new ThreeSectionLayout(
                $this->mNavigation = new OrderedListNavigator()
            )
        );
    }

    /**
     * Add HTML Container Content
     * @param IRenderHTML $Content
     * @return String|void always returns void
     */
    function addContent(IRenderHTML $Content) {
        $this->mLayout->addContent($Content);
    }

    /**
     * Render request as html
     * @param IRequest $Request the IRequest instance for this render which contains the request and remaining args
     * @param \CPath\Render\HTML\Attribute\IAttributes $Attr optional attributes for the input field
     * @return String|void always returns void
     */
    function renderHTML(IRequest $Request, IAttributes $Attr = null) {
        $this->mResponseBody->renderHTML($Request);
    }
}