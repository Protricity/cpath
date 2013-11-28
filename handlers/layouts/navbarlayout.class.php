<?php
namespace CPath\Handlers\Layouts;

use CPath\Base;
use CPath\Interfaces\IRequest;
use CPath\Misc\RenderIndents as RI;

abstract class NavBarLayout extends SimpleBodyLayout {

//    public function __construct($Target, ITheme $Theme=NULL) {
//        parent::__construct($Target, $Theme);
//    }


    protected function setupHeadFields() {
        parent::setupHeadFields();
        //$basePath = Base::getClassPublicPath(__CLASS__, false);
        //$this->addHeadStyleSheet($basePath . 'assets/navbarlayout.css');
    }

    /**
     * Render the navigation bar content
     * @param IRequest $Request the IRequest instance for this render
     * @return void
     */
    abstract protected function renderNavBarContent(IRequest $Request);

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
        echo RI::ni(-1), "<div class='navbar'>";
            $this->renderNavBarContent($Request);
        echo RI::ni(-1), "</div>";
        echo RI::ni(-1), "<div class='content'>";
            $this->renderCenterContent($Request);
        echo RI::ni(-1), "</div>";
        RI::ai(-1);
    }

}

