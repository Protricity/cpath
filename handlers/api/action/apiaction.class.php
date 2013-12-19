<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Handlers\Api\Action;

use CPath\Actions\Action;
use CPath\Base;
use CPath\Describable\IDescribable;
use CPath\Handlers\Api\Interfaces\IAPI;
use CPath\Handlers\Interfaces\IView;
use CPath\Handlers\Themes\Interfaces\ITheme;
use CPath\Handlers\Views\APIView;
use CPath\Interfaces\IRequest;



abstract class APIAction extends Action {
    private $mAPI=null, $mAPIView=null, $mTheme;
    function __construct(ITheme $Theme=null) {
        $this->mTheme = $Theme;
    }

    /**
     * @return IAPI
     */
    abstract protected function loadAPI();

    private function getAPI() {
        return $this->mAPI ?: $this->mAPI = $this->loadAPI();
    }

    private function getAPIView() {
        return $this->mAPIView ?: $this->mAPIView = new APIView($this->getAPI(), null, null, $this->mTheme);
    }

    /**
     * Provide head elements to any IView
     * Note: If an IView encounters this object, it should attempt to add support scripts to it's header by using this method
     * @param IView $View
     */
    function addHeadElementsToView(IView $View) {
        parent::addHeadElementsToView($View);

        $basePath = Base::getClassPublicPath($this, false);
        $View->addHeadStyleSheet($basePath . 'assets/apiactions.css', true);
        $View->addHeadScript($basePath . 'assets/apiactions.js', true);

        $this->getAPIView()->addHeadElementsToView($View);
    }

    /**
     * Get the Object Description
     * @return IDescribable|String a describable Object, or string describing this object
     */
    function getDescribable() {
        return $this->getAPI();
    }

    /**
     * Filter this action according to the present circumstances
     * @param IRequest $Request
     * @return bool true if this action should execute. Return not true if this action does not apply
     */
    function execute(IRequest $Request) {
        return $this->getAPI()->execute($Request);
    }

    /**
     * Render the fragment content
     * @param IRequest $Request the IRequest instance for this render
     * @return void
     */
    function renderFragmentContent(IRequest $Request) {
        $this->getAPIView()->renderForm($Request, false, 'api-action'); // TODO: serialize?
    }
//
//    // Static
//
//    static function getFromAPI(IAPI $API, ITheme $Theme=null) {
//        return new APIActionAvailable($API, $Theme);
//    }
}
//
//class APIActionAvailable extends APIAction {
//
//    /**
//     * Filter this action according to the present circumstances
//     * @return bool true if this action is available. Return not true if this action is not available
//     */
//    function isAvailable() { return true; }
//
//    /**
//     * Called when an exception occurred. This should capture exceptions that occur in ::execute and ::filter
//     * @param IRequest $Request
//     * @param \Exception $Ex
//     * @return void
//     */
//    function onException(IRequest $Request, \Exception $Ex) {}
//
//    /**
//     * Called when a request to store the action in persistent data has been made.
//     * Warning: This method may perform storage of the action in rapid succession.
//     * @param IRequest $Request
//     * @return void
//     */
//    function onStore(IRequest $Request) {}
//}