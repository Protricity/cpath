<?php
namespace CPath\Framework\API\Fragments;

use CPath\Base;
use CPath\Config;
use CPath\Framework\Api\Interfaces\IAPI;
use CPath\Framework\Render\Attribute\IAttributes;
use CPath\Framework\Render\IRender;
use CPath\Framework\Request\Interfaces\IRequest;
use CPath\Handlers\Interfaces\IView;
use CPath\Handlers\Themes\CPathDefaultTheme;
use CPath\Handlers\Themes\Interfaces\ITableTheme;
use CPath\Handlers\Util\HTMLRenderUtil;
use CPath\Interfaces\IViewConfig;

abstract class AbstractFormFragment implements IRender, IViewConfig{

    private $mTheme;

    /**
     * @param ITableTheme $Theme
     * @internal param \CPath\Framework\Api\Interfaces\IAPI $API
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
        $basePath = Base::getClassPublicPath($this, false);
        $View->addHeadStyleSheet($basePath . 'assets/formfragment.css', true);
        $View->addHeadScript($basePath . 'assets/formfragment.js', true);
    }

    /**
     * Render this API Form
     * @param IRequest $Request the IRequest instance for this render
     * @param \CPath\Framework\Render\Attribute\IAttributes|NULL $Attr optional attributes to add to the content
     * @return void
     */
    abstract function renderForm(IRequest $Request, IAttributes $Attr=NULL);

    public function getTheme() { return $this->mTheme; }

    /**
     * Render this handler
     * @param IRequest $Request the IRequest instance for this render
     * @param \CPath\Framework\Render\Attribute\IAttributes|NULL $Attr optional attributes to add to the content
     * @return void
     */
    function render(IRequest $Request, IAttributes $Attr=null)
    {
        $this->renderForm($Request, $Attr);
    }

    protected function renderFormButtons(IRequest $Request) {
        $Util = new HTMLRenderUtil($Request);
        $Util->button('Submit', 'form-button-submit');
    }
}
