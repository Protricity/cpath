<?php
namespace CPath\Handlers\Views;

use CPath\Config;
use CPath\Describable\Describable;
use CPath\Framework\API\Fragments\APIDebugFormFragment;
use CPath\Framework\API\Fragments\APIFormFragment;
use CPath\Framework\API\Fragments\APIResponseBoxFragment;
use CPath\Framework\Api\Interfaces\IAPI;
use CPath\Framework\Route\Render\IDestination;
use CPath\Framework\Render\Util\RenderIndents as RI;
use CPath\Framework\Request\Interfaces\IRequest;
use CPath\Framework\Response\Interfaces\IResponse;
use CPath\Framework\Route\Map\Common\CallbackRouteMap;
use CPath\Framework\Route\Routable\IRoutable;
use CPath\Handlers\Themes\Interfaces\ITheme;
use CPath\Interfaces\IViewConfig;

class APIMultiView extends AbstractAPIView {
    private $mResponse = null;
    /** @var APIFormFragment */
    private $mForm;
    private $mResponseBox;
    private $mSelectedAPI;

    private $mTarget;

    /** @var \CPath\Framework\Route\Render\IDestination[]  */
    private $mRoutes;

    /**
     * @param \CPath\Framework\Route\Routable\IRoutable $Routable
     * @param IResponse $Response
     * @param ITheme $Theme
     */
    public function __construct(IRoutable $Routable, IResponse $Response=null, ITheme $Theme=null) {
        parent::__construct($Theme);
        $Routes = array();
        $Routable->mapRoutes(new CallbackRouteMap($Routable, function($prefix, IDestination $Destination) use (&$Routes) {
            $Routes[$prefix] = $Destination;
        }));
        $this->mRoutes = $Routes;
        $this->mResponse = $Response;
        $this->mTarget = $Routable;

        //if(!$Routes)
        //    throw new \InvalidArgumentException("No APIs found in handlers");

        $this->mResponseBox = new APIResponseBoxFragment($this->getTheme());
    }

    private function getHandlerFromRequest(IRequest $Request) {
        if($this->mSelectedAPI)
            return $this->mSelectedAPI;

        $prefixes = array_keys($this->mRoutes);
        $args = $this->getArgs();

        if(is_numeric($args[0]) && isset($prefixes[intval($args[0])]))
            $Renderer = $this->mRoutes[$prefixes[$args[0]]];
        elseif(isset($this->mRoutes[$args[0]]))
            $Renderer = $this->mRoutes[$args[0]];
        else
            $Renderer = $this->mRoutes[$prefixes[0]];

        return $this->mSelectedAPI = $Renderer;
    }

    /**
     * Set up <head> element fields for this View
     * @param IRequest $Request
     * @throws \InvalidArgumentException
     */
    final protected function setupAPIHeadFields(IRequest $Request) {
        $API = $this->getHandlerFromRequest($Request);

        if(!$API instanceof IAPI) {
            throw new \InvalidArgumentException(get_class($API) . " does not implement IAPI");
        }

        $this->mForm = new APIDebugFormFragment($API, $this->getTheme());
        $this->mForm->addHeadElementsToView($this);

        if($API instanceof IViewConfig)
            $API->addHeadElementsToView($this);

        if($this->mResponseBox instanceof IViewConfig)
            $this->mResponseBox->addHeadElementsToView($this);
    }


    /**
     * Render the navigation bar content
     * @param IRequest $Request the IRequest instance for this render
     * @return void
     */
    protected function renderNavBarContent(IRequest $Request) {
        $prefixes = array_keys($this->mRoutes);
        foreach($prefixes as $i => $prefix) {
            $Destination = $this->mRoutes[$prefix];
            if(!$Destination instanceof IAPI)
                continue;
            $Describable = Describable::get($Destination);
            $this->renderNavBarEntry( $i . '#' . $prefix, $Describable);
        }
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
        $API = $this->getHandlerFromRequest($Request);
        $route = $Request->getMethod() . ' ' . $Request->getPath();
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
}
