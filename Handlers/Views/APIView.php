<?php
namespace CPath\Handlers\Views;

use CPath\Base;
use CPath\Config;
use CPath\Describable\Describable;
use CPath\Framework\API\Fragments\APIDebugFormFragment;
use CPath\Framework\API\Fragments\APIResponseBoxFragment;
use CPath\Framework\Api\Interfaces\IAPI;
use CPath\Framework\Api\Util\APIExecuteUtil;
use CPath\Framework\Render\IRender;
use CPath\Framework\Render\Util\RenderIndents as RI;
use CPath\Framework\Request\Interfaces\IRequest;
use CPath\Framework\Response\Interfaces\IResponse;
use CPath\Framework\Response\Util\ResponseUtil;
use CPath\Handlers\Layouts\NavBarLayout;
use CPath\Handlers\Themes\CPathDefaultTheme;
use CPath\Handlers\Themes\Interfaces\ITheme;
use CPath\Interfaces\IViewConfig;

class APIView extends NavBarLayout implements IRender {

    private $mAPI = null;
    private $mForm, $mResponseBox;
    private $mResponse = null;

    public function __construct(IAPI $API, IResponse $Response=null, ITheme $Theme=null) {
        parent::__construct($Theme ?: CPathDefaultTheme::get());

        $this->mAPI = $API;
        $this->mResponse = $Response;

        $this->mForm = new APIDebugFormFragment($this->mAPI);
        $this->mResponseBox = new APIResponseBoxFragment($this->getTheme());
    }

    /**
     * Set up <head> element fields for this View
     * @param IRequest $Request
     * @return void
     */
    protected function setupHeadFields(IRequest $Request)
    {
        if($this->mAPI instanceof IViewConfig)
            $this->mAPI->addHeadElementsToView($this);
        $this->mForm->addHeadElementsToView($this);
        if($this->mResponseBox instanceof IViewConfig)
            $this->mResponseBox->addHeadElementsToView($this);

        $basePath = Base::getClassPublicPath($this);
        $this->addHeadScript($basePath . 'assets/vkbeautify.min.js');
    }

    /**
     * Render the main view content
     * @param IRequest $Request the IRequest instance for this render
     * @return void
     */
    function renderViewContent(IRequest $Request) {
        $this->mForm->renderHtml($Request);
        $this->mResponseBox->renderResponseBox($Request);
    }

    /**
     * Render the header
     * @param IRequest $Request the IRequest instance for this render
     * @return void
     */
    protected function renderBodyHeaderContent(IRequest $Request) {
        $API = $this->mAPI;
        $route = $Request->getMethod() . ' ' . $Request->getPath();
        echo RI::ni(), "<h1>{$route}</h1>";
        echo RI::ni(), "<h2>", Describable::get($API)->getDescription(), "</h2>";
    }

    /**
     * Render the header
     * @param IRequest $Request the IRequest instance for this render
     * @return void
     */
    protected function renderBodyFooterContent(IRequest $Request) {
        // TODO: Implement renderBodyFooterContent() method.
    }

    /**
     * Render the navigation bar content
     * @param IRequest $Request the IRequest instance for this render
     * @return void
     */
    protected function renderNavBarContent(IRequest $Request) {
//        $RouteSet = $Request->getRoute();
//        if($RouteSet instanceof RoutableSet) {
//            $Util = new RouteUtil($RouteSet);
//            $Routes = $RouteSet->getRoutes();
//
//            $ids = $this->getRoutableSetIDs($RouteSet);
//            foreach($ids as $i=>$prefix) {
//                /** @var IRoute $Route */
//                $Route = $Routes[$prefix];
//                $Handler = $Route->loadHandler();
//                if(!$Handler instanceof IAPI)
//                    continue;
//                $Describable = Describable::get($Handler);
//                $this->renderNavBarEntry($Util->buildPublicURL(true) . '/' . $i . '#' . $Route->getPrefix(), $Describable);
//            }
//        }
    }

    /**
     * Render request as JSON
     * @param IRequest $Request the IRequest instance for this render which contains the request and remaining args
     * @return String|void always returns void
     */
    function renderJSON(IRequest $Request)
    {
        $ExecUtil = new APIExecuteUtil($this->mAPI);
        $Response = $ExecUtil->executeOrCatch($Request, $this->getArgs());
        $ResponseUtil = new ResponseUtil($Response);
        $ResponseUtil->renderJSON($Request, true);
    }

    /**
     * Render request as plain text
     * @param IRequest $Request the IRequest instance for this render which contains the request and remaining args
     * @return String|void always returns void
     */
    function renderText(IRequest $Request)
    {
        $ExecUtil = new APIExecuteUtil($this->mAPI);
        $Response = $ExecUtil->executeOrCatch($Request, $this->getArgs());
        $ResponseUtil = new ResponseUtil($Response);
        $ResponseUtil->renderText($Request, true);
    }

    /**
     * Render request as xml
     * @param IRequest $Request the IRequest instance for this render which contains the request and remaining args
     * @param string $rootElementName Optional name of the root element
     * @return String|void always returns void
     */
    function renderXML(IRequest $Request, $rootElementName = 'root')
    {
        $ExecUtil = new APIExecuteUtil($this->mAPI);
        $Response = $ExecUtil->executeOrCatch($Request, $this->getArgs());
        $ResponseUtil = new ResponseUtil($Response);
        $ResponseUtil->renderXML($Request, $rootElementName, true);
    }
}
