<?php
namespace CPath\Handlers\Layouts;

use CPath\Interfaces\IRequest;
use CPath\Misc\RenderIndents as RI;

abstract class SideBarLeftLayout extends SimpleBodyLayout {

    /**
     * Render the header
     * @param IRequest $Request the IRequest instance for this render
     * @return void
     */
    abstract protected function renderLeftSideBarContent(IRequest $Request);

    /**
     * Render the page center content
     * @param IRequest $Request the IRequest instance for this render
     * @return void
     */
    abstract protected function renderCenterContent(IRequest $Request);

    /**
     * Render the page center content
     * @param IRequest $Request the IRequest instance for this render
     * @return void
     */
    final protected function renderBodyContent(IRequest $Request) {
        RI::ai(1);
        echo RI::ni(-1), "<div class='body-sidebar-left'>";
        $this->renderLeftSideBarContent($Request);
        echo RI::ni(-1), "</div>";
        echo RI::ni(-1), "<div class='body-center'>";
        $this->renderCenterContent($Request);
        echo RI::ni(-1), "</div>";
        RI::ai(-1);
    }

}

