<?php
namespace CPath\Handlers\Views;

use CPath\Base;
use CPath\Config;
use CPath\Describable\Describable;
use CPath\Framework\API\Fragments\APIDebugFormFragment;
use CPath\Framework\API\Fragments\APIFormFragment;
use CPath\Framework\API\Fragments\APIResponseBoxFragment;
use CPath\Framework\Api\Interfaces\IAPI;
use CPath\Framework\Api\Util\APIExecuteUtil;
use CPath\Framework\Render\IRender;
use CPath\Framework\Render\Util\RenderIndents as RI;
use CPath\Framework\Request\Interfaces\IRequest;
use CPath\Framework\Response\Util\ResponseUtil;
use CPath\Handlers\Layouts\NavBarLayout;
use CPath\Handlers\Themes\Interfaces\ITheme;
use CPath\Interfaces\IViewConfig;

class APIMultiView extends NavBarLayout implements IRender {
    /** @var APIFormFragment */
    private $mForm;
    private $mResponseBox;
    private $mSelectedAPI;

    /** @var IAPI[]  */
    private $mAPIs = array();

    /**
     * @param ITheme $Theme
     */
    public function __construct(ITheme $Theme=null) {
        parent::__construct($Theme);
        $this->mResponseBox = new APIResponseBoxFragment($this->getTheme());
    }


    function addAPI($prefix, IAPI $API) {
        $this->mAPIs[$prefix] = $API;
    }

    private function getHandlerFromRequest() {
        if($this->mSelectedAPI)
            return $this->mSelectedAPI;

        $prefixes = array_keys($this->mAPIs);
        $args = $this->getArgs();

        if(is_numeric($args[0]) && isset($prefixes[intval($args[0])]))
            $Renderer = $this->mAPIs[$prefixes[$args[0]]];
        elseif(isset($this->mAPIs[$args[0]]))
            $Renderer = $this->mAPIs[$args[0]];
        else
            $Renderer = $this->mAPIs[$prefixes[0]];

        return $this->mSelectedAPI = $Renderer;
    }


    /**
     * Set up <head> element fields for this View
     * @param IRequest $Request
     * @throws \InvalidArgumentException
     * @return void
     */
    protected function setupHeadFields(IRequest $Request)
    {
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

        $basePath = Base::getClassPublicPath($this);
        $this->addHeadScript($basePath . 'assets/vkbeautify.min.js');
    }


    /**
     * Render the navigation bar content
     * @param IRequest $Request the IRequest instance for this render
     * @return void
     */
    protected function renderNavBarContent(IRequest $Request) {
        $prefixes = array_keys($this->mAPIs);
        foreach($prefixes as $i => $prefix) {
            $Destination = $this->mAPIs[$prefix];
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
        $this->mForm->renderHtml($Request);
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

    /**
     * Render request as JSON
     * @param IRequest $Request the IRequest instance for this render which contains the request and remaining args
     * @return String|void always returns void
     */
    function renderJSON(IRequest $Request)
    {
        $ExecUtil = new APIExecuteUtil($this->getHandlerFromRequest($Request));
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
        $ExecUtil = new APIExecuteUtil($this->getHandlerFromRequest($Request));
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
        $ExecUtil = new APIExecuteUtil($this->getHandlerFromRequest($Request));
        $Response = $ExecUtil->executeOrCatch($Request, $this->getArgs());
        $ResponseUtil = new ResponseUtil($Response);
        $ResponseUtil->renderXML($Request, $rootElementName, true);
    }
}
