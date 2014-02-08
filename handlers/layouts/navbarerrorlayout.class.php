<?php
namespace CPath\Handlers\Layouts;

use CPath\Describable\Describable;
use CPath\Handlers\Themes\Interfaces\ITheme;
use CPath\Handlers\Themes\Util\TableThemeUtil;
use CPath\Framework\Request\Interfaces\IRequest;

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

    /**
     * Add additional <head> element fields for this View
     * @param IRequest $Request
     * @return void
     */
    abstract protected function addHeadFields(IRequest $Request);

    /**
     * Set up <head> element fields for this View
     * @param IRequest $Request
     * @return void
     */
    final protected function setupHeadFields(IRequest $Request) {
        $this->setTitle(Describable::get($this->mException)->getTitle());
        $this->addHeadFields($Request);
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

