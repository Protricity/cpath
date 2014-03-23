<?php
namespace CPath\Handlers\Layouts;

use CPath\Describable\Describable;
use CPath\Describable\IDescribable;
use CPath\Framework\Render\Attribute\Attr;
use CPath\Framework\Render\Util\RenderIndents as RI;
use CPath\Framework\Request\Interfaces\IRequest;
use CPath\Handlers\Interfaces\IRenderContent;
use CPath\Handlers\Themes\Interfaces\ITheme;
use CPath\Handlers\View;

abstract class NavBarLayout extends View implements IRenderContent {

    private $navBarStarted=false;

    public function __construct(ITheme $Theme=NULL) {
        parent::__construct($Theme);
    }

//
//    /**
//     * Set up <head> element fields for this View
//     * @param IRequest $Request
//     */
//    protected function setupHeadFields(IRequest $Request) {
//        parent::setupHeadFields();
//        //$basePath = Base::getClassPublicPath(__CLASS__);
//        //$this->addHeadStyleSheet($basePath . 'assets/navbarlayout.css');
//    }

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


    protected function renderBodyHeader(IRequest $Request) {
        $this->getTheme()->renderSectionStart($Request, Attr::get('header'));
        $this->renderBodyHeaderContent($Request);
        $this->getTheme()->renderSectionEnd($Request);
    }


    /**
     * Render the view body
     * @param IRequest $Request the IRequest instance for this render
     * @return void
     */
    final function renderBody(IRequest $Request) {
        $this->getTheme()->renderBodyStart($Request);

        $this->renderBodyHeader($Request);
        $this->renderBodyContent($Request);
        $this->renderBodyFooter($Request);

        $this->getTheme()->renderBodyEnd($Request);
    }

    /**
     * Render the page center content
     * @param IRequest $Request the IRequest instance for this render
     * @return void
     */
    final protected function renderBodyContent(IRequest $Request) {
        $Theme = $this->getTheme();
        $Theme->renderSectionStart($Request, Attr::get('navbar'));
        $this->renderNavBarContent($Request);
        if($this->navBarStarted) {
            echo RI::ni(), "</ul>";
            $this->navBarStarted = false;
        }
        $Theme->renderSectionEnd($Request);

        $Theme->renderSectionStart($Request, Attr::get('content'));
        $this->renderViewContent($Request);
        $Theme->renderSectionEnd($Request);
    }


    protected function renderBodyFooter(IRequest $Request) {
        $this->getTheme()->renderSectionStart($Request, Attr::get('footer'));
        $this->renderBodyFooterContent($Request);
        $this->getTheme()->renderSectionEnd($Request);
    }
}

