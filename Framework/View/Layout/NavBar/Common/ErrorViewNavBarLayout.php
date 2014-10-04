<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 6/10/14
 * Time: 10:54 PM
 */
namespace CPath\Framework\View\Layout\NavBar\Common;

use CPath\Config;
use CPath\Describable\Describable;
use CPath\Framework\Render\Theme\Interfaces\ITheme;
use CPath\Framework\Render\Util\RenderIndents as RI;
use CPath\Framework\Request\Common\ExceptionRequestWrapper;
use CPath\Framework\View\Layout\NavBar\AbstractNavBarLayout;
use CPath\Render\HTML\Attribute\IAttributes;
use CPath\Render\HTML\Theme\Util\TableThemeUtil;
use CPath\Request\IRequest;

class ErrorViewNavBarLayout extends AbstractNavBarLayout
{
    private $mException;

    public function __construct(\Exception $Exception=NULL, ITheme $Theme=NULL) {
        parent::__construct($Theme);
        $this->mException = $Exception;
    }

    private function getException(IRequest $Request) {
        if($this->mException)
            return $this->mException;

        if($Request instanceof ExceptionRequestWrapper) {
            return $this->mException = $Request->getException();
        } else {
            return new \Exception("No exception provided");
        }
    }

    /**
     * Set up <head> element fields for this View
     * @param IRequest $Request
     * @return void
     */
    protected function setupHeadFields(IRequest $Request)
    {
        $Exception = $this->getException($Request);
        $this->setTitle(Describable::get($Exception)->getTitle());
    }

    /**
     * Render the header
     * @param IRequest $Request the IRequest instance for this render
     * @return void
     */
    protected function renderBodyHeaderContent(IRequest $Request)
    {
        $Exception = $this->getException($Request);
        echo RI::ni(), "Error: ", $Exception->getMessage();
    }

    /**
     * Render the main view content
     * @param IRequest $Request the IRequest instance for this render
     * @param \CPath\Render\HTML\Attribute\IAttributes $Attr
     * @return void
     */
    protected function renderBodyContent(IRequest $Request, IAttributes $Attr = null)
    {
        $Exception = $this->getException($Request);
        $Util = new TableThemeUtil($Request, $this->getTheme());
        $Util->renderStart("An exception has occurred");
        $Util->renderTR(array($Exception->getMessage()), true);
        $Util->renderTD("<code>" . $Exception . "</code>");
        $Util->renderEnd();
    }

    /**
     * Render the header
     * @param IRequest $Request the IRequest instance for this render
     * @return void
     */
    protected function renderBodyFooterContent(IRequest $Request)
    {
        // TODO: Implement renderBodyFooterContent() method.
    }

    /**
     * Render the navigation bar content
     * @param IRequest $Request the IRequest instance for this render
     * @return void
     */
    protected function renderNavBarContent(IRequest $Request)
    {
        // TODO: Implement renderNavBarContent() method.
    }
}