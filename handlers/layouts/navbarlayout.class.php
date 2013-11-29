<?php
namespace CPath\Handlers\Layouts;

use CPath\Base;
use CPath\Handlers\Interfaces\IRenderContent;
use CPath\Handlers\View;
use CPath\Interfaces\IRequest;
use CPath\Misc\RenderIndents as RI;

abstract class NavBarLayout extends View implements IRenderContent {

//    public function __construct($Target, ITheme $Theme=NULL) {
//        parent::__construct($Target, $Theme);
//    }


    protected function setupHeadFields() {
        parent::setupHeadFields();
        //$basePath = Base::getClassPublicPath(__CLASS__, false);
        //$this->addHeadStyleSheet($basePath . 'assets/navbarlayout.css');
    }

    /**
     * Render the header
     * @param IRequest $Request the IRequest instance for this render
     * @return void
     */
    abstract protected function renderBodyHeaderContent(IRequest $Request);


    /**
     * Render the header
     * @param IRequest $Request the IRequest instance for this render
     * @return void
     */
    abstract protected function renderBodyFooterContent(IRequest $Request);

    /**
     * Render the navigation bar content
     * @param IRequest $Request the IRequest instance for this render
     * @return void
     */
    abstract protected function renderNavBarContent(IRequest $Request);


    protected function renderBodyHeader(IRequest $Request) {
        echo RI::ni(), "<div class='header'>";
        RI::ai(1);
        $this->renderBodyHeaderContent($Request);
        RI::ai(-1);
        echo RI::ni(), "</div>";
    }


    /**
     * Render the view body
     * @param IRequest $Request the IRequest instance for this render
     * @return void
     */
    final function renderBody(IRequest $Request) {
        echo RI::ni(), "<body>";
        echo RI::ni(1), "<div class='page'>";
        RI::ai(2);

        $this->renderBodyHeader($Request);
        $this->renderBodyContent($Request);
        $this->renderBodyFooter($Request);

        RI::ai(-2);
        echo RI::ni(1), "</div>";
        echo RI::ni(), "</body>";
    }

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
            $this->renderViewContent($Request);
        echo RI::ni(-1), "</div>";
        RI::ai(-1);
    }


    protected function renderBodyFooter(IRequest $Request) {
        echo RI::ni(), "<div class='footer'>";
        RI::ai(1);
        $this->renderBodyFooterContent($Request);
        RI::ai(-1);
        echo RI::ni(), "</div>";
    }
}

