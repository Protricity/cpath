<?php
namespace CPath\Framework\Render\Layout\API;

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
use CPath\Framework\Render\IRenderAll;
use CPath\Framework\Render\Util\RenderIndents as RI;
use CPath\Framework\Request\Common\ModifiedRequestWrapper;
use CPath\Request\IRequest;
use CPath\Framework\Response\Interfaces\IResponse;
use CPath\Handlers\Response\ResponseUtil;
use CPath\Route\RouteNotFoundException;
use CPath\Route\IRouteMap;
use CPath\Route\IRoutable;
use CPath\Framework\View\API\AbstractNavBarLayout;
use CPath\Framework\Render\Layout\API\APILayout;
use CPath\Framework\View\API\IWrapper;
use CPath\Framework\Render\Theme\Interfaces\ITheme;
use CPath\Interfaces\IViewConfig;
use CPath\Backend\CPathBackendRoutes;

class APIMultiView extends AbstractNavBarLayout implements IRenderAll, IAPI, IRoutable {
    /** @var APIFormFragment */
    private $mForm;
    private $mResponseBox;

    /** @var IAPI[]  */
    private $mAPIs = array();

    private $mArgs = array();
    private $mTargetClass;

    /**
     * @param mixed $Target
     * @param \CPath\Framework\Render\Theme\Interfaces\ITheme $Theme
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
     * @param \CPath\Request\IRequest $Request the IRequest instance for this render
     * @return String|void always returns void
     */
    function render(IRequest $Request) {
        parent::render($Request);
    }

    private function selectAPI(IRequest &$Request) {
        try {
            $Routes = new CPathBackendRoutes();
            $Render = $Routes->routeRequest($Request, $this);

            if($Render instanceof IWrapper)
                $Render = $Render->getWrappedObject();
            return $Render;
        } catch (RouteNotFoundException $ex) {

        }
        $args = $Request->getArgs();
        $prefixes = array_keys($this->mAPIs);
        if(isset($args[0]) && $args[0] !== '') {
            if(is_numeric($args[0]) && isset($prefixes[intval($args[0])])) {
                $arg = array_shift($args);
                $API = $this->mAPIs[$prefixes[$arg]];
                $Request = new ModifiedRequestWrapper($Request, $args);
                return $API;
            } elseif (isset($this->mAPIs[$args[0]])) {
                $arg = array_shift($args);
                $API = $this->mAPIs[$arg];
                $Request = new ModifiedRequestWrapper($Request, $args);
                return $API;
            } else {
                return $this->mAPIs[$prefixes[0]];
            }

        }

        throw new RouteNotFoundException("Could not match API: " . $Request->getMethodName() . " " . $Request->getPath());
//        else
//            return $this->mAPIs[$prefixes[0]];
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
     * @param \CPath\Request\IRequest $Request
     * @throws \InvalidArgumentException
     * @return void
     */
    protected function setupHeadFields(IRequest $Request)
    {
        try {
            $API = $this->selectAPI($Request);

            if(!$API instanceof IAPI) {
                throw new \InvalidArgumentException(get_class($API) . " does not implement IAPI");
            }

            $this->mForm = new APIDebugFormFragment($API, $this->getTheme());

            if($API instanceof IViewConfig)
                $API->addHeadElementsToView($this);
        } catch (RouteNotFoundException $ex) {
            $this->mForm = new APIFormFragment($this, $this->getTheme());
        }

        $this->mForm->addHeadElementsToView($this);

        if($this->mResponseBox instanceof IViewConfig)
            $this->mResponseBox->addHeadElementsToView($this);

        $basePath = Base::getClassPath($this, true);
        $this->addHeadScript($basePath . 'assets/vkbeautify.min.js');
    }


    /**
     * Render the navigation bar content
     * @param \CPath\Request\IRequest $Request the IRequest instance for this render
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
     * @param \CPath\Request\IRequest $Request the IRequest instance for this render
     * @return void
     */
    function renderPageContent(IRequest $Request) {
        $this->mForm->renderHTML($Request);
        $this->mResponseBox->renderResponseBox($Request);
    }

    /**
     * Render the header
     * @param \CPath\Request\IRequest $Request the IRequest instance for this render
     * @return void
     */
    protected function renderBodyHeaderContent(IRequest $Request) {

        $route = $Request->getMethodName() . ' ' . $Request->getPath();
        echo RI::ni(), "<h1>{$route}</h1>";

        try {
            $API = $this->selectAPI($Request);
            echo RI::ni(), "<h2>", Describable::get($API)->getDescription(), "</h2>";
        } catch (RouteNotFoundException $ex) {

        }
    }

    /**
     * Render the header
     * @param \CPath\Request\IRequest $Request the IRequest instance for this render
     * @return void
     */
    protected function renderBodyFooterContent(IRequest $Request)
    {
        // TODO: Implement renderBodyFooterContent() method.
    }

    /**
     * Render request as JSON
     * @param \CPath\Request\IRequest $Request the IRequest instance for this render which contains the request and remaining args
     * @return String|void always returns void
     */
    function renderJSON(IRequest $Request)
    {
        $API = $this->selectAPI($Request);
        $ExecUtil = new APIExecuteUtil($API);
        $Response = $ExecUtil->execute($Request);
        $ResponseUtil = new ResponseUtil($Response);
        $ResponseUtil->renderJSON($Request, true);
    }

    /**
     * Render request as plain text
     * @param \CPath\Request\IRequest $Request the IRequest instance for this render which contains the request and remaining args
     * @return String|void always returns void
     */
    function renderText(IRequest $Request)
    {
        $API = $this->selectAPI($Request);
        $ExecUtil = new APIExecuteUtil($API);
        $Response = $ExecUtil->execute($Request);
        $ResponseUtil = new ResponseUtil($Response);
        $ResponseUtil->renderText($Request, true);
    }

    /**
     * Render request as xml
     * @param \CPath\Request\IRequest $Request the IRequest instance for this render which contains the request and remaining args
     * @param string $rootElementName Optional name of the root element
     * @return String|void always returns void
     */
    function renderXML(IRequest $Request, $rootElementName = 'root')
    {
        $API = $this->selectAPI($Request);
        $ExecUtil = new APIExecuteUtil($API);
        $Response = $ExecUtil->execute($Request);
        $ResponseUtil = new ResponseUtil($Response);
        $ResponseUtil->renderXML($Request, $rootElementName, true);
    }

    /**
     * Execute this API Endpoint with the entire request.
     * @param IRequest $Request the IRequest instance for this render which contains the request and args
     * @internal param Array $args additional arguments for this execution
     * @return IResponse the api call response with data, message, and status
     */
    function execute(IRequest $Request) {
        $API = $this->selectAPI($Request);
        return $API->execute($Request);
    }

    /**
     * Get all API Fields
     * @param \CPath\Request\IRequest $Request the IRequest instance for this render which contains the request and args
     * @return IField[]|IFieldCollection
     */
    function getFields(IRequest $Request) {
        try {
            $API = $this->selectAPI($Request);
            return $API->getFields($Request);
        } catch (RouteNotFoundException $ex) {
            return array();
        }
    }

    /**
     * Returns the route for this IRender
     * @param \CPath\Route\IRouteMap $Map
     */
    function mapRoutes(IRouteMap $Map) {
        foreach($this->mAPIs as $prefix => $API) {
            if(strpos($prefix, ' ') !== false) {
                list($method, $path) = explode(' ', $prefix, 2);
                if($path[0] !== '/') {
                    $path = strtolower(Base::getClassPath($this->mTargetClass, false)) . $path;
                }
            } else {
                $method = $prefix;
                $path = strtolower(Base::getClassPath($this->mTargetClass, false));
            }
            $prefix = $method . ' ' . $path;
            $Map->mapRoute($prefix, new \CPath\Framework\Render\Layout\API\APILayout($API));
        }
    }
}
