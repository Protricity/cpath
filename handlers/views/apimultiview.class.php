<?php
namespace CPath\Handlers\Views;

use CPath\Config;
use CPath\Describable\Describable;
use CPath\Framework\Api\Interfaces\IAPI;
use CPath\Handlers\API\Fragments\APIDebugFormFragment;
use CPath\Handlers\API\Fragments\APIFormFragment;
use CPath\Handlers\API\Fragments\APIResponseBoxFragment;
use CPath\Handlers\Themes\Interfaces\ITheme;
use CPath\Framework\Request\Interfaces\IRequest;
use CPath\Interfaces\IViewConfig;
use CPath\Misc\RenderIndents as RI;
use CPath\Framework\Response\Interfaces\IResponse;
use CPath\Route\IRoutable;
use CPath\Route\IRoute;
use CPath\Route\RoutableSet;
use CPath\Route\RoutableSetWrapper;
use CPath\Route\RouteUtil;

class APIMultiView extends AbstractAPIView {
    private $mResponse = null;
    /** @var APIFormFragment */
    private $mForm;
    private $mResponseBox;
    /** @var IRoute[]|RoutableSet */
    private $mRoutes=array();
    private $mSelectedAPI;
    private $mSelectedRoute;

    /**
     * @param RoutableSet $Routes
     * @param \CPath\Framework\Response\IResponse $Response
     * @param ITheme $Theme
     * @throws \InvalidArgumentException
     */
    public function __construct(RoutableSet $Routes, IResponse $Response=null, ITheme $Theme=null) {
        $this->mRoutes = $Routes;
        $this->mResponse = $Response;

        //if(!$Routes)
        //    throw new \InvalidArgumentException("No APIs found in handlers");

        $this->mResponseBox = new APIResponseBoxFragment($this->getTheme());
        parent::__construct($Theme);
    }

    /**
     * Set up <head> element fields for this View
     * @param IRequest $Request
     */
    final protected function setupAPIHeadFields(IRequest $Request) {
        $Routes = $this->mRoutes;


        $ids = $this->getRoutableSetIDs($Routes);

        $arg = (int)$Request->getNextArg();
        if($arg && isset($ids[$arg]))
            $Route = $this->mRoutes[$ids[$arg]];
        elseif($Routes->hasDefault())
            $Route = $Routes->getDefault();
        else
            $Route = $this->mRoutes[0];

        $this->mSelectedAPI = $Route->loadHandler();
        $this->mSelectedRoute = $Route;

        if(!$this->mSelectedAPI instanceof IAPI) {
            //$Route = $Routes->getDefault();
            //$this->mSelectedAPI = $Route->loadHandler();
            //if(!$this->mSelectedAPI instanceof IAPI)
                throw new \InvalidArgumentException(get_class($this->mSelectedAPI) . " does not implement IAPI");
        }

        $this->mForm = new APIDebugFormFragment($this->mSelectedAPI, $this->getTheme());
        $this->mForm->addHeadElementsToView($this);

        $this->mSelectedAPI->addHeadElementsToView($this);

        if($this->mResponseBox instanceof IViewConfig)
            $this->mResponseBox->addHeadElementsToView($this);
    }


    /**
     * Render the navigation bar content
     * @param IRequest $Request the IRequest instance for this render
     * @return void
     */
    protected function renderNavBarContent(IRequest $Request) {
        $Routes = $this->mRoutes;
        $Route = $Request->getRoute();
        $Util = new RouteUtil($Route);

        $ids = $this->getRoutableSetIDs($Routes);
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

    /**
     * Render the main view content
     * @param IRequest $Request the IRequest instance for this render
     * @return void
     */
    function renderViewContent(IRequest $Request) {
        $WrappedRequest = new RoutableSetWrapper($Request, $this->mRoutes, $this->mSelectedRoute);
        $this->mForm->render($WrappedRequest);
        $this->mResponseBox->renderResponseBox($WrappedRequest);
    }

    /**
     * Render the header
     * @param IRequest $Request the IRequest instance for this render
     * @return void
     */
    protected function renderBodyHeaderContent(IRequest $Request) {
        $API = $this->mSelectedAPI;
        if($API instanceof IRoutable)
            $Route = $API->loadRoute();
        else
            $Route = $Request->getRoute();
        $route = $Route->getPrefix();
        echo RI::ni(), "<h1>{$route}</h1>";
        echo RI::ni(), "<h2>", Describable::get($API)->getDescription(), "</h2>";
    }

    /**
     * Render the header
     * @param IRequest $Request the IRequest instance for this render
     * @return void
     */
    protected function renderBodyFooterContent(IRequest $Request)
    {
        // TODO: Implement renderBodyFooterContent() method.
    }

    /**
     * Add additional actions to this view Manager
     * @param IActionManager $Manager
     * @return void
     */
    protected function addActions(IActionManager $Manager)
    {
        // TODO: Implement addActions() method.
    }


}
