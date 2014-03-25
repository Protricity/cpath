<?php
namespace CPath\Handlers\Views;

use CPath\Base;
use CPath\Config;
use CPath\Describable\Describable;
use CPath\Framework\API\Field\Collection\Interfaces\IFieldCollection;
use CPath\Framework\API\Field\Interfaces\IField;
use CPath\Framework\API\Fragments\APIDebugFormFragment;
use CPath\Framework\API\Fragments\APIFormFragment;
use CPath\Framework\API\Fragments\APIResponseBoxFragment;
use CPath\Framework\API\Interfaces\IAPI;
use CPath\Framework\API\Util\APIExecuteUtil;
use CPath\Framework\Data\Wrapper\IWrapper;
use CPath\Framework\Render\IRenderAll;
use CPath\Framework\Render\Util\RenderIndents as RI;
use CPath\Framework\Request\Interfaces\IRequest;
use CPath\Framework\Response\Interfaces\IResponse;
use CPath\Framework\Response\Util\ResponseUtil;
use CPath\Framework\Route\Exceptions\RouteNotFoundException;
use CPath\Framework\Route\Map\IRouteMap;
use CPath\Framework\Route\Routable\IRoutable;
use CPath\Handlers\Layouts\NavBarLayout;
use CPath\Handlers\Themes\Interfaces\ITheme;
use CPath\Interfaces\IViewConfig;
use CPath\Routes;

class APIMultiView extends NavBarLayout implements IRenderAll, IAPI, IRoutable {
    /** @var APIFormFragment */
    private $mForm;
    private $mResponseBox;
    private $mSelectedAPI;

    /** @var IAPI[]  */
    private $mAPIs = array();

    private $mArgs = array();
    private $mTargetClass;

    /**
     * @param mixed $Target
     * @param ITheme $Theme
     */
    public function __construct($Target, ITheme $Theme=null) {
        parent::__construct($Theme);
        $this->mTargetClass = get_class($Target);
        $this->mResponseBox = new APIResponseBoxFragment($Theme);
    }


    function addAPI($prefix, IAPI $API) {
        $this->mAPIs[$prefix] = $API;
    }

    /**
     * Render this request
     * @param IRequest $Request the IRequest instance for this render
     * @return String|void always returns void
     */
    function render(IRequest $Request) {
        $API = $this->selectAPI($Request);
        if($API instanceof IWrapper)
            $API = $API->getWrappedObject();
        $this->mSelectedAPI = $API;
        parent::render($Request);
    }

    private function selectAPI(IRequest $Request) {
        try {
            $Routes = new Routes();
            return $Routes->routeRequest($Request, $this);
        } catch (RouteNotFoundException $ex) {

        }

        $prefixes = array_keys($this->mAPIs);
        $args = $this->mArgs;
        if(!empty($args[0])) {
            if(is_numeric($args[0]) && isset($prefixes[intval($args[0])]))
                return $this->mAPIs[$prefixes[$args[0]]];
            elseif(isset($this->mAPIs[$args[0]]))
                return $this->mAPIs[$args[0]];
            else
                return $this->mAPIs[$prefixes[0]];
        }
        else
            return $this->mAPIs[$prefixes[0]];
    }
//
//    /**
//     * @return IAPI
//     * @throws \InvalidArgumentException
//     */
//    public function getSelectedAPI() {
//        if(!$this->mSelectedAPI)
//            throw new \InvalidArgumentException("API has not been selected");
//        return $this->mSelectedAPI;
//    }

    /**
     * Set up <head> element fields for this View
     * @param IRequest $Request
     * @throws \InvalidArgumentException
     * @return void
     */
    protected function setupHeadFields(IRequest $Request)
    {
        $API = $this->selectAPI($Request);
        $this->mSelectedAPI = $API;

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
        $API = $this->selectAPI($Request);
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
        $API = $this->selectAPI($Request);
        $ExecUtil = new APIExecuteUtil($API);
        $Response = $ExecUtil->executeOrCatch($Request, $this->mArgs);
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
        $API = $this->selectAPI($Request);
        $ExecUtil = new APIExecuteUtil($API);
        $Response = $ExecUtil->executeOrCatch($Request, $this->mArgs);
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
        $API = $this->selectAPI($Request);
        $ExecUtil = new APIExecuteUtil($API);
        $Response = $ExecUtil->executeOrCatch($Request, $this->mArgs);
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
        $API = $this->selectAPI($Request);
        return $API->execute($Request, $args);
    }

    /**
     * Get all API Fields
     * @param IRequest $Request the IRequest instance for this render which contains the request and args
     * @return IField[]|IFieldCollection
     */
    function getFields(IRequest $Request) {
        $API = $this->selectAPI($this->mSelectedAPI);
        return $API->getFields($Request);
    }

    /**
     * Returns the route for this IRender
     * @param IRouteMap $Map
     */
    function mapRoutes(IRouteMap $Map) {
        foreach($this->mAPIs as $prefix => $API) {
            if(strpos($prefix, ' ') !== false) {
                list($method, $path) = explode(' ', $prefix, 2);
                if($path[0] !== '/')
                    $path = '/' . str_replace('\\', '/', strtolower(dirname($this->mTargetClass))) . '/' . $path;
            } else {
                $method = $prefix;
                $path = '/' . str_replace('\\', '/', strtolower($this->mTargetClass));
            }
            $prefix = $method . ' ' . $path;
            $Map->mapRoute($prefix, new APIView($API));
        }
    }
}
