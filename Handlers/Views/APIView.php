<?php
namespace CPath\Handlers\Views;

use CPath\Config;
use CPath\Describable\Describable;
use CPath\Framework\API\Fragments\APIDebugFormFragment;
use CPath\Framework\API\Fragments\APIResponseBoxFragment;
use CPath\Framework\Api\Interfaces\IAPI;
use CPath\Framework\Render\Util\RenderIndents as RI;
use CPath\Framework\Request\Interfaces\IRequest;
use CPath\Framework\Response\Interfaces\IResponse;
use CPath\Handlers\Themes\Interfaces\ITheme;
use CPath\Interfaces\IViewConfig;
use CPath\Route\IRoute;
use CPath\Route\RoutableSet;
use CPath\Route\RouteUtil;

class APIView extends AbstractAPIView {

    private $mAPI = null;
    private $mForm, $mResponseBox;
    private $mResponse = null;
    private $mRoute;

    public function __construct(IAPI $API, IRoute $Route=null, IResponse $Response=null, ITheme $Theme=null) {
        $this->mAPI = $API;
        $this->mRoute = $Route;
        $this->mResponse = $Response;

        $this->mForm = new APIDebugFormFragment($this->mAPI);
        $this->mResponseBox = new APIResponseBoxFragment($this->getTheme());
        parent::__construct($Theme);
    }

    /**
     * Set up <head> element fields for this View
     * @param IRequest $Request
     */
    final protected function setupAPIHeadFields(IRequest $Request) {
        if($this->mAPI instanceof IViewConfig)
            $this->mAPI->addHeadElementsToView($this);
        $this->mForm->addHeadElementsToView($this);
        if($this->mResponseBox instanceof IViewConfig)
            $this->mResponseBox->addHeadElementsToView($this);
    }

    /**
     * Render the main view content
     * @param IRequest $Request the IRequest instance for this render
     * @return void
     */
    function renderViewContent(IRequest $Request) {
        $this->mForm->render($Request);
        $this->mResponseBox->renderResponseBox($Request);
    }

    /**
     * Render the header
     * @param IRequest $Request the IRequest instance for this render
     * @return void
     */
    protected function renderBodyHeaderContent(IRequest $Request) {
        $API = $this->mAPI;
        $Route = $this->mRoute ?: $Request->getRoute();
        $route = $Route->getPrefix();
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
        $RouteSet = $Request->getRoute();
        if($RouteSet instanceof RoutableSet) {
            $Util = new RouteUtil($RouteSet);
            $Routes = $RouteSet->getRoutes();

            $ids = $this->getRoutableSetIDs($RouteSet);
            foreach($ids as $i=>$prefix) {
                /** @var IRoute $Route */
                $Route = $Routes[$prefix];
                $Handler = $Route->loadHandler();
                if(!$Handler instanceof IAPI)
                    continue;
                $Describable = Describable::get($Handler);
                $this->renderNavBarEntry($Util->buildPublicURL(true) . '/' . $i . '#' . $Route->getPrefix(), $Describable);
            }
        }
    }
}
