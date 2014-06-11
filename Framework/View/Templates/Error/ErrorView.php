<?php
namespace CPath\Framework\View\Templates\Error;

use API\Themes\DefaultTheme;
use CPath\Config;
use CPath\Framework\PDO\Table\Types\PDOTable;
use CPath\Framework\Render\Util\RenderIndents as RI;
use CPath\Framework\Request\Interfaces\IRequest;
use CPath\Framework\View\Templates\Layouts\NavBar\AbstractErrorNavBarLayout;
use CPath\Framework\View\Theme\Interfaces\ITheme;
use CPath\Framework\View\Theme\Interfaces\IThemeAggregate;

class ErrorView extends AbstractErrorNavBarLayout {
    public function __construct(\Exception $Exception, PDOTable $Table, ITheme $Theme=null) {
        if(!$Theme) {
            if($Table instanceof IThemeAggregate)
                $Theme = $Table->loadTheme();
            else
                $Theme = DefaultTheme::getError();
        }
        parent::__construct($Exception, $Theme);
    }

    /**
     * Add additional <head> element fields for this View
     * @param \CPath\Framework\Request\Interfaces\IRequest $Request
     * @return void
     */
    protected function addHeadFields(IRequest $Request)
    {
        // TODO: Implement addHeadFields() method.
    }


    protected function sendHeaders($message=NULL, $code=NULL, $mimeType=NULL) {
        /** @var \Exception $Exception */
        $Exception = $this->getException();
        parent::sendHeaders($message ?: $Exception->getMessage(), $code ?: 400, $mimeType);
    }

    /**
     * Render the navigation bar content
     * @param IRequest $Request the IRequest instance for this render
     * @return void
     */
    protected function renderNavBarContent(IRequest $Request)
    {
        echo 'navbar';
    }

    /**
     * Render the header
     * @param IRequest $Request the IRequest instance for this render
     * @return void
     */
    final protected function renderBodyHeaderContent(IRequest $Request)
    {
        echo RI::ni(), "Error: ", Config::getSiteName();
    }

    /**
     * Render the header
     * @param IRequest $Request the IRequest instance for this render
     * @return void
     */
    final protected function renderBodyFooterContent(IRequest $Request)
    {
        //echo RI::ni(), Config::getSiteName();
    }
}
