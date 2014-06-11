<?php
namespace CPath\Framework\View\Templates\Layouts\NavBar;

use CPath\Base;
use CPath\Describable\Describable;
use CPath\Describable\IDescribable;
use CPath\Framework\Render\Attribute\Attr;
use CPath\Framework\Render\Attribute\IAttributes;
use CPath\Framework\Render\Util\RenderIndents as RI;
use CPath\Framework\Request\Interfaces\IRequest;
use CPath\Framework\View\Common\AbstractView;
use CPath\Framework\View\Theme\Interfaces\ITheme;

abstract class AbstractNavBarLayout extends AbstractView {

    private $navBarStarted=false;

    public function __construct(ITheme $Theme=NULL) {
        parent::__construct($Theme);
        $basePath = Base::getClassPath(__CLASS__);
        $this->addHeadStyleSheet($basePath . 'assets/navbarlayout.css');
    }

    /**
     * Render the header
     * @param IRequest $Request the IRequest instance for this render
     * @return void
     */
    abstract protected function renderBodyHeaderContent(IRequest $Request);

    /**
     * Render the main view content
     * @param IRequest $Request the IRequest instance for this render
     * @param IAttributes $Attr
     * @return void
     */
    abstract protected function renderPageContent(IRequest $Request, IAttributes $Attr = null);

    /**
     * Render the navigation bar content
     * @param IRequest $Request the IRequest instance for this render
     * @return void
     */
    abstract protected function renderNavBarContent(IRequest $Request);

    /**
     * Render the header
     * @param IRequest $Request the IRequest instance for this render
     * @return void
     */
    abstract protected function renderBodyFooterContent(IRequest $Request);

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

    /**
     * Render the html body
     * @param IRequest $Request the IRequest instance for this render
     * @param IAttributes $Attr optional attributes for the input field
     * @return void
     */
    final protected function renderBody(IRequest $Request, IAttributes $Attr = null) {
        $Theme = $this->getTheme();
        $Theme->renderSectionStart($Request, Attr::get('navbar'));
        $this->renderNavBarContent($Request);
        if($this->navBarStarted) {
            echo RI::ni(), "</ul>";
            $this->navBarStarted = false;
        }
        $Theme->renderSectionEnd($Request);

        $Theme->renderSectionStart($Request, Attr::get('content'));
        $this->renderPageContent($Request, $Attr);
        $Theme->renderSectionEnd($Request);
    }

}


