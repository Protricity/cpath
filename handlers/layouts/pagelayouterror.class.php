<?php
namespace CPath\Handlers\Layouts;

use CPath\Handlers\Interfaces\IRenderContent;
use CPath\Handlers\Themes\Interfaces\ITheme;
use CPath\Handlers\Themes\Util\TableThemeUtil;
use CPath\Handlers\View;
use CPath\Helpers\Describable;
use CPath\Interfaces\IRequest;
use CPath\Misc\RenderIndents as RI;

abstract class PageLayoutError extends PageLayout {
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
        $Util = new TableThemeUtil($Request, $this->$this->getTheme());
        //$Theme->renderTableStart($Request, $this->mException->getMessage());
        $Util->renderTD($this->mException);
    }
}

