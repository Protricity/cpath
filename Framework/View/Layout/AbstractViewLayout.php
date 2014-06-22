<?php
namespace CPath\Framework\View\Layout;

use CPath\Base;
use CPath\Framework\Render\Attribute\Attr;
use CPath\Framework\Render\Attribute\IAttributes;
use CPath\Framework\Request\Interfaces\IRequest;
use CPath\Framework\View\Common\AbstractView;
use CPath\Framework\View\Theme\Interfaces\ITheme;

abstract class AbstractViewLayout extends AbstractView {

    public function __construct(ITheme $Theme=NULL) {
        parent::__construct($Theme);
        $basePath = Base::getClassPath(__CLASS__);
        $this->addHeadStyleSheet($basePath . 'assets/pagelayout.css');
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
    abstract protected function renderBodyContent(IRequest $Request, IAttributes $Attr = null);

    /**
     * Render the header
     * @param IRequest $Request the IRequest instance for this render
     * @return void
     */
    abstract protected function renderBodyFooterContent(IRequest $Request);

    protected function renderBodyHeader(IRequest $Request) {
        $this->getTheme()->renderSectionStart($Request, Attr::get('header'));
        $this->renderBodyHeaderContent($Request);
        $this->getTheme()->renderSectionEnd($Request);
    }

    /**
     * Render the html body
     * @param IRequest $Request the IRequest instance for this render
     * @param IAttributes $Attr optional attributes for the input field
     * @return void
     */
    final protected function renderBody(IRequest $Request, IAttributes $Attr = null) {
        $this->getTheme()->renderBodyStart($Request);

        $this->renderBodyHeader($Request);

        // Body Section

        $this->getTheme()->renderSectionStart($Request, Attr::get('body'));
        $this->renderBodyContent($Request, $Attr);
        $this->getTheme()->renderSectionEnd($Request);

        $this->renderBodyFooter($Request);

        $this->getTheme()->renderBodyEnd($Request);
    }

    protected function renderBodyFooter(IRequest $Request) {
        $this->getTheme()->renderSectionStart($Request, Attr::get('footer'));
        $this->renderBodyFooterContent($Request);
        $this->getTheme()->renderSectionEnd($Request);
    }
}

