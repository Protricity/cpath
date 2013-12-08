<?php
namespace CPath\Handlers\Layouts;

use CPath\Handlers\Themes\Interfaces\ITheme;
use CPath\Handlers\Themes\Util\TableThemeUtil;
use CPath\Helpers\Describable;
use CPath\Interfaces\IRequest;

abstract class NavBarErrorLayout extends NavBarLayout {

    const RESPONSE_CODE = 400;
    const RESPONSE_MESSAGE = 'OK';

    private $mException;
    public function __construct(\Exception $Exception, ITheme $Theme) {
        parent::__construct($Theme);
        $this->mException = $Exception;
    }

    public function getException() {
        return $this->mException;
    }

    protected function setupHeadFields() {
        $this->addHeadHTML("<title>" . Describable::get($this->mException)->getTitle() . "</title>", self::FIELD_TITLE);
        parent::setupHeadFields();
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

