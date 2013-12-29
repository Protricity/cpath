<?php
namespace CPath\Handlers\Views;

use CPath\Actions\IActionManager;
use CPath\Config;
use CPath\Describable\Describable;
use CPath\Handlers\API\Fragments\APIDebugFormFragment;
use CPath\Handlers\API\Fragments\APIResponseBoxFragment;
use CPath\Handlers\Api\Interfaces\IAPI;
use CPath\Handlers\Themes\Interfaces\ITheme;
use CPath\Interfaces\IRequest;
use CPath\Response\IResponse;
use CPath\Interfaces\IViewConfig;
use CPath\Misc\RenderIndents as RI;
use CPath\Route\IRoute;

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
