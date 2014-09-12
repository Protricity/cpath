<?php
namespace CPath\Framework\View\Templates\CLI;

use API\Themes\DefaultTheme;
use CPath\Config;
use CPath\Framework\PDO\Table\Types\PDOTable;
use CPath\Render\HTML\Attribute\IAttributes;
use CPath\Request\IRequest;
use CPath\Framework\View\Layout\NavBar\Common\ErrorViewNavBarLayout;
use CPath\Framework\Render\Theme\Interfaces\ITheme;
use CPath\Framework\Render\Theme\Interfaces\IThemeAggregate;

class CLIErrorView extends ErrorViewNavBarLayout {
    public function __construct(\Exception $Exception, PDOTable $Table, ITheme $Theme=null) {
        if(!$Theme) {
            if($Table instanceof IThemeAggregate)
                $Theme = $Table->loadTheme();
            else
                $Theme = DefaultTheme::getError();
        }
        parent::__construct($Exception, $Theme);
    }

    protected function sendHTTPHeaders($message=NULL, $code=NULL, $mimeType=NULL) {
        /** @var \Exception $Exception */
        $Exception = $this->getException();
        parent::sendHTTPHeaders($message ?: $Exception->getMessage(), $code ?: 400, $mimeType);
    }

    /**
     * Render the main view content
     * @param IRequest $Request the IRequest instance for this render
     * @param \CPath\Render\HTML\Attribute\IAttributes $Attr
     * @return void
     */
    protected function renderBodyContent(IRequest $Request, IAttributes $Attr = null)
    {
        // TODO: Implement renderBodyContent() method.
    }
}
