<?php
namespace CPath\Handlers\Views;

use CPath\Actions\IActionManager;
use CPath\Config;
use CPath\Describable\Describable;
use CPath\Handlers\API\Fragments\APIDebugFormFragment;
use CPath\Handlers\API\Fragments\APIFormFragment;
use CPath\Handlers\API\Fragments\APIResponseBoxFragment;
use CPath\Handlers\Api\Interfaces\IAPI;
use CPath\Handlers\Themes\Interfaces\ITheme;
use CPath\Interfaces\IHandler;
use CPath\Interfaces\IRequest;
use CPath\Response\IResponse;
use CPath\Interfaces\IViewConfig;
use CPath\Misc\RenderIndents as RI;
use CPath\Route\IRoutable;
use CPath\Route\IRoute;
use CPath\Route\RoutableSet;

class APIMultiView extends AbstractAPIView {
    private $mResponse = null;
    /** @var APIFormFragment */
    private $mForm;
    private $mResponseBox;
    private $mRoutes=array();
    private $mSelectedAPI;

    /**
     * @param RoutableSet $Routes
     * @param IResponse $Response
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

        $arg = (int)$Request->getNextArg();
        if($arg && isset($this->mRoutes[$arg]))
            $Route = $this->mRoutes[$arg];
        elseif($Routes->hasDefault())
            $Route = $Routes->getDefault();
        else
            $Route = $this->mRoutes[0];

        $this->mSelectedAPI = $Route->loadHandler();

        if(!$this->mSelectedAPI instanceof IAPI)
            throw new \InvalidArgumentException(get_class($this->mSelectedAPI) . " does not implement IAPI");

        $this->mForm = new APIDebugFormFragment($this->mSelectedAPI, $this->getTheme());
        $this->mForm->addHeadElementsToView($this);

        $this->mSelectedAPI->addHeadElementsToView($this);

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
    protected function renderBodyHeaderContent(IRequest $Request)
    {
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
     * Render the navigation bar content
     * @param IRequest $Request the IRequest instance for this render
     * @return void
     */
    protected function renderNavBarContent(IRequest $Request) {
        foreach($this->mRoutes as $i=>$Route) {
            $Handler = $Route->loadHandler();
            $Describable = Describable::get($Handler);
            $route = $this->mRoutes->getPrefix();
            $this->renderNavBarEntry($route . '/' . $i . '#' . $Describable->getTitle(), $Describable);
        }
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
