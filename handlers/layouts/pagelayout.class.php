<?php
namespace CPath\Handlers\Layouts;

use CPath\Handlers\Interfaces\IRenderContent;
use CPath\Handlers\View;
use CPath\Interfaces\IRequest;
use CPath\Misc\RenderIndents as RI;

abstract class PageLayout extends View implements IRenderContent {

//    public function __construct($Target, ITheme $Theme=NULL) {
//        parent::__construct($Target, $Theme);
//    }

    protected function setupHeadFields() {
        parent::setupHeadFields();
        //$basePath = Base::getClassPublicPath(__CLASS__, false);
        //$this->addHeadStyleSheet($basePath . 'assets/pagelayout.css');
    }

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


    protected function renderBodyHeader(IRequest $Request) {
        $this->getTheme()->renderSectionStart($Request, 'header');
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
        $this->renderViewContent($Request);
        $this->renderBodyFooter($Request);

        $this->getTheme()->renderBodyEnd($Request);
    }

    protected function renderBodyFooter(IRequest $Request) {
        $this->getTheme()->renderSectionStart($Request, 'footer');
        $this->renderBodyFooterContent($Request);
        $this->getTheme()->renderSectionEnd($Request);
    }
}

