<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/3/14
 * Time: 3:36 PM
 */
namespace CPath\Handlers\HTML\Navigation;

use CPath\Framework\Render\Header\IHeaderWriter;
use CPath\Framework\Render\Header\IHTMLSupportHeaders;
use CPath\Framework\Render\Util\RenderIndents as RI;
use CPath\Render\HTML\Attribute\IAttributes;
use CPath\Request\IRequest;

class OrderedListNavigator extends \CPath\Handlers\HTML\Navigation\AbstractNavigator implements IHTMLSupportHeaders
{
    /**
     * Begin NavBar render
     * @param \CPath\Request\IRequest $Request the IRequest instance for this render which contains the request and remaining args
     * @param \CPath\Render\Attribute\\CPath\Render\HTML\Attribute\IAttributes $Attr optional attributes for the input field
     * @return String|void always returns void
     */
    function renderStart(IRequest $Request, IAttributes $Attr = null) {
        echo RI::ni(), "<ul class='navbar-ordered-list'>";
        RI::ai(1);
    }

    /**
     * Render NavBar link
     * @param IRequest $Request
     * @param \CPath\Handlers\HTML\Navigation\RouteLink $Link
     * @return String|void always returns void
     */
    function renderLink(IRequest $Request, \CPath\Handlers\HTML\Navigation\RouteLink $Link) {
        echo RI::ni(), "<li class='navbar-menu-item clearfix'>";
        echo RI::ni(1), $Link->getHyperlink();
        echo RI::ni(), "</li>";
    }

    /**
     * @param \CPath\Request\IRequest $Request the IRequest instance for this render which contains the request and remaining args
     * @return String|void always returns void
     */
    function renderEnd(IRequest $Request) {
        RI::ai(-1);
        echo RI::ni(), "</ul>";
    }

    /**
     * Write all support headers used by this IView instance
     * @param \CPath\Request\IRequest $Request
     * @param IHeaderWriter $Head the writer instance to use
     * @return String|void always returns void
     */
    function writeHeaders(IRequest $Request, IHeaderWriter $Head) {
        $Head->writeStyleSheet(__NAMESPACE__ . '\assets\ordered-list-navigator.css');
    }
}