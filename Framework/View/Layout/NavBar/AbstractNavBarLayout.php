<?php
namespace CPath\Framework\View\Layout\NavBar;

use CPath\Base;
use CPath\Describable\Describable;
use CPath\Describable\IDescribable;
use CPath\Framework\Render\Theme\Interfaces\ITheme;
use CPath\Framework\Render\Util\RenderIndents as RI;
use CPath\Render\HTML\Layout\ContentLayout;
use CPath\Request\IRequest;


abstract class AbstractNavBarLayout extends ContentLayout {

    private $navBarStarted=false;

    public function __construct(ITheme $Theme=NULL) {
        parent::__construct($Theme);
    }

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

}

