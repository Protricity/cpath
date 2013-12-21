<?php
namespace CPath\Handlers\API\Fragments;

use CPath\Base;
use CPath\Config;
use CPath\Handlers\Api\Interfaces\IAPI;
use CPath\Handlers\Interfaces\IView;
use CPath\Handlers\Themes\CPathDefaultTheme;
use CPath\Handlers\Themes\Interfaces\ITableTheme;
use CPath\Handlers\Util\HTMLRenderUtil;
use CPath\Interfaces\IHandler;
use CPath\Interfaces\IRequest;
use CPath\Interfaces\IViewConfig;

abstract class AbstractFormFragment implements IHandler, IViewConfig{

    private $mAPI, $mTheme;

    /**
     * @param IAPI $API
     * @param ITableTheme $Theme
     */
    public function __construct(IAPI $API, ITableTheme $Theme = null) {
        $this->mAPI = $API;
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
     * @param String|Array|NULL $class element classes
     * @param String|Array|NULL $attr element attributes
     * @return void
     */
    abstract function renderForm(IRequest $Request, $class=null, $attr=null);

    public function getAPI() { return $this->mAPI; }
    public function getTheme() { return $this->mTheme; }

    /**
     * Render this handler
     * @param IRequest $Request the IRequest instance for this render
     * @param String|Array|NULL $class element classes
     * @param String|Array|NULL $attr element attributes
     * @return void
     */
    function render(IRequest $Request, $class=null, $attr=null)
    {
        $Util = new HTMLRenderUtil($Request);
        $this->renderForm($Request, $Util->getClass($class, 'fragment-form'), $attr);
    }

    protected function renderFormButtons(IRequest $Request) {
        $Util = new HTMLRenderUtil($Request);
        $Util->button('Submit', 'form-button-submit');
    }
}
