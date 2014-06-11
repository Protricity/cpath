<?php
namespace CPath\Framework\API\Fragments;

use CPath\Base;
use CPath\Config;
use CPath\Framework\Render\Attribute\IAttributes;
use CPath\Framework\Render\HTML\IRenderHTML;
use CPath\Framework\Request\Interfaces\IRequest;
use CPath\Framework\View\IView;
use CPath\Framework\View\Theme\CPathDefaultTheme;
use CPath\Framework\View\Theme\Interfaces\ITableTheme;
use CPath\Framework\View\Util\HTMLRenderUtil;
use CPath\Interfaces\IViewConfig;

abstract class AbstractFormFragment implements IRenderHTML, IViewConfig{

    private $mTheme;

    /**
     * @param ITableTheme $Theme
     * @internal param \CPath\Framework\API\Interfaces\IAPI $API
     */
    public function __construct(ITableTheme $Theme = null) {
        $this->mTheme = $Theme ?: CPathDefaultTheme::get();
    }

    /**
     * Provide head elements to any IView
     * Note: If an IView encounters this object, it should attempt to add support scripts to it's header by using this method
     * @param IView $View
     */
    function addHeadElementsToView(IView $View) {
        $basePath = Base::getClassPath($this, true);

        $View->addHeadScript($basePath . 'assets/jquery.min.js');

        $View->addHeadScript($basePath . 'assets/cpath.js');

        $View->addHeadStyleSheet($basePath . 'assets/api.css');
        $View->addHeadScript($basePath . 'assets/api.js');

        $View->addHeadScript($basePath . 'assets/form.js');
        $View->addHeadStyleSheet($basePath . 'assets/formfragment.css');
        $View->addHeadScript($basePath . 'assets/formfragment.js');
    }

    /**
     * Render this API Form
     * @param IRequest $Request the IRequest instance for this render
     * @param IAttributes|NULL $Attr optional attributes to add to the content
     * @return void
     */
    abstract function renderForm(IRequest $Request, IAttributes $Attr=NULL);

    public function getTheme() { return $this->mTheme; }

    /**
     * Render request as html and sends headers as necessary
     * @param IRequest $Request the IRequest instance for this render which contains the request and remaining args
     * @param IAttributes $Attr optional attributes for the input field
     * @return void
     */
    function renderHtml(IRequest $Request, IAttributes $Attr = null)
    {
        $this->renderForm($Request, $Attr);
    }

    protected function renderFormButtons(IRequest $Request) {
        $Util = new HTMLRenderUtil($Request);
        $Util->button('Submit', 'form-button-submit');
    }
}
