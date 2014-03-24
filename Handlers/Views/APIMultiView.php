<?php
namespace CPath\Handlers\Views;

use CPath\Base;
use CPath\Config;
use CPath\Describable\Describable;
use CPath\Framework\Api\Field\Collection\Interfaces\IFieldCollection;
use CPath\Framework\Api\Field\Interfaces\IField;
use CPath\Framework\API\Fragments\APIDebugFormFragment;
use CPath\Framework\API\Fragments\APIFormFragment;
use CPath\Framework\API\Fragments\APIResponseBoxFragment;
use CPath\Framework\Api\Interfaces\IAPI;
use CPath\Framework\Api\Util\APIExecuteUtil;
use CPath\Framework\Render\IRenderAll;
use CPath\Framework\Render\Util\RenderIndents as RI;
use CPath\Framework\Request\Interfaces\IRequest;
use CPath\Framework\Response\Interfaces\IResponse;
use CPath\Framework\Response\Util\ResponseUtil;
use CPath\Handlers\Layouts\NavBarLayout;
use CPath\Handlers\Themes\Interfaces\ITheme;
use CPath\Interfaces\IViewConfig;

class APIMultiView extends NavBarLayout implements IRenderAll, IAPI {
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
        $path = $this->getPath();
        $method = $this->getMethod();
        foreach($this->mAPIs as $prefix => $API) {
            list($m, $p) = explode(' ', $prefix);
            if($m === 'ANY' || $m == $this->getMethod()) {
                if(strpos($requestPath = $path, $p) === 0) {
                    $argPath = substr($requestPath, strlen($path));
                    $this->setArgs(explode('/', trim($argPath, '/')));
                    return $this->mSelectedAPI = $API;
                }
            }
        }

        if(!empty($args[0])) {
            if(is_numeric($args[0]) && isset($prefixes[intval($args[0])]))
                $Renderer = $this->mAPIs[$prefixes[$args[0]]];
            elseif(isset($this->mAPIs[$args[0]]))
                $Renderer = $this->mAPIs[$args[0]];
            else
                $Renderer = $this->mAPIs[$prefixes[0]];
        }
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
        $API = $this->getHandlerFromRequest();

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
        $API = $this->getHandlerFromRequest();
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
        $ExecUtil = new APIExecuteUtil($this->getHandlerFromRequest());
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
        $ExecUtil = new APIExecuteUtil($this->getHandlerFromRequest());
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
        $ExecUtil = new APIExecuteUtil($this->getHandlerFromRequest());
        $Response = $ExecUtil->executeOrCatch($Request, $this->getArgs());
        $ResponseUtil = new ResponseUtil($Response);
        $ResponseUtil->renderXML($Request, $rootElementName, true);
    }

    /**
     * Execute this API Endpoint with the entire request.
     * @param IRequest $Request the IRequest instance for this render which contains the request and args
     * @param Array $args additional arguments for this execution
     * @return IResponse the api call response with data, message, and status
     */
    function execute(IRequest $Request, $args) {
        return $this->getHandlerFromRequest()
            ->execute($Request, $args);
    }

    /**
     * Get all API Fields
     * @return IField[]|IFieldCollection
     */
    function getFields() {
        return $this->getHandlerFromRequest()
            ->getFields();
    }
}
