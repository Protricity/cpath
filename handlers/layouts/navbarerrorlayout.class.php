<?php
namespace CPath\Handlers\Layouts;

use CPath\Base;
use CPath\Handlers\Interfaces\IRenderContent;
use CPath\Handlers\Themes\Interfaces\ITheme;
use CPath\Handlers\Themes\Util\TableThemeUtil;
use CPath\Handlers\View;
use CPath\Interfaces\IRequest;
use CPath\Misc\RenderIndents as RI;

abstract class NavBarErrorLayout extends NavBarLayout {

    const RESPONSE_CODE = 400;
    const RESPONSE_MESSAGE = 'OK';

    private $mException;
    public function __construct(\Exception $Exception, ITheme $Theme) {
        parent::__construct($Exception, $Theme);
        $this->mException = $Exception;
    }

    /**
     * Render the main view content
     * @param IRequest $Request the IRequest instance for this render
     * @return void
     */
    function renderViewContent(IRequest $Request)
    {
        $Util = new TableThemeUtil($Request, $this->getTheme());
        $Util->renderStart("An exception has occurred");
        $Util->renderTR(array($this->mException->getMessage()), true);
        $Util->renderTD("<code>" . $this->mException . "</code>");
        $Util->renderEnd();
    }
}

