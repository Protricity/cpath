<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/1/14
 * Time: 9:38 PM
 */
namespace CPath\Framework\Render\Fragment\Common;

use CPath\Describable\Describable;
use CPath\Describable\IDescribable;
use CPath\Framework\Render\Attribute\IAttributes;
use CPath\Framework\Render\HTML\IRenderHTML;
use CPath\Framework\Request\Interfaces\IRequest;
use CPath\Framework\Render\Util\RenderIndents as RI;


class NavBarFragment implements IRenderHTML
{

    /**
     * Render request as html
     * @param IRequest $Request the IRequest instance for this render which contains the request and remaining args
     * @param IAttributes $Attr optional attributes for the input field
     * @return String|void always returns void
     */
    function renderHTML(IRequest $Request, IAttributes $Attr = null)
    {
        // TODO: Implement renderHTML() method.
    }


    /**
     * Render the navigation bar content
     * @param String $url the url for this navbar entry
     * @param String|IDescribable $description the description of this nave entry
     * @return void
     */
    protected function renderNavBarEntry($url, $description)
    {
        $Describable = Describable::get($description);
        if(!$this->navBarStarted) {
            echo RI::ni(), "<ul class='navbar-menu'>";
            $this->navBarStarted = true;
        }

        echo RI::ni(1), "<li class='navbar-menu-item clearfix'>";
        echo RI::ni(2), "<a href='{$url}' title='", $Describable->getTitle(), "'>", $Describable->getDescription(), "</a>";
        echo RI::ni(1), "</li>";
    }

}