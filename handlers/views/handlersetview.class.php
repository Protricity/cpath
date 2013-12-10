<?php
namespace CPath\Handlers\Views;

use CPath\Base;
use CPath\Builders\RouteBuilder;
use CPath\Config;
use CPath\Handlers\Api\Interfaces\IAPI;
use CPath\Handlers\InvalidRouteException;
use CPath\Handlers\Layouts\NavBarLayout;
use CPath\Handlers\Themes\CPathDefaultTheme;
use CPath\Handlers\Themes\Interfaces\ITheme;
use CPath\Handlers\Themes\Util\TableThemeUtil;
use CPath\Helpers\Describable;
use CPath\Interfaces\IHandlerAggregate;
use CPath\Interfaces\IHandlerSet;
use CPath\Interfaces\ILogEntry;
use CPath\Interfaces\ILogListener;
use CPath\Interfaces\IRequest;
use CPath\Log;
use CPath\Misc\RenderIndents as RI;

class HandlerSetView extends NavBarLayout implements ILogListener {

    const BUILD_IGNORE = true;
    private $mLog = array();
    /** @var NavBarLayout */
    private $mPageLayout = null;
    /** @var IHandlerSet */
    private $mHandlers = null;
    private $mHandlerIDs = null;

    public function __construct(ITheme $Theme=null) {
        parent::__construct($Theme ?: CPathDefaultTheme::get());
        if(Config::$Debug)
            Log::addCallback($this);
        //<link rel="stylesheet" href="<?php echo $basePath; assets/apiview.css" />
        //<base href="<?php echo $basePath; " />
    }


    protected function setupHeadFields() {
        parent::setupHeadFields();
        $basePath = Base::getClassPublicPath($this, false);
        $this->addHeadStyleSheet($basePath . 'assets/apiview.css');
        $this->addHeadScript($basePath . 'assets/apiview.js');
    }

    function onLog(ILogEntry $log) {
        $this->mLog[] = $log;
    }

    /**
     * Render the main view content
     * @param IRequest $Request the IRequest instance for this render
     * @return void
     */
    function renderViewContent(IRequest $Request)
    {
        $this->processRequest($Request);
        if($this->mPageLayout) {
            $this->mPageLayout->renderViewContent($Request);
            return;
        }

        $routes = $this->mHandlers->getAllRoutes(new RouteBuilder());
        //$basePath = Base::getClassPublicPath($this);
        list(,$infoPath) = explode(' ', $Request->getRoute()->getPrefix(), 2);
        $infoPath = substr(Config::getDomainPath(), 0, -1) . $infoPath .'/';

        $num = 1;

        $Table = new TableThemeUtil($Request, $this->getTheme());

        $Table->renderStart("Endpoints", 'handlersetview-table');
        $Table->renderHeaderStart();
        $Table->renderTD('#',           0, 'table-field-num');
        $Table->renderTD('Route',       0, 'table-field-route');
        $Table->renderTD('Description', 0, 'table-field-description');
        $Table->renderTD('Destination', 0, 'table-field-destination');
        foreach($this->mHandlers as $route => $Handler) {
            $description = "No Description";
            try{
                $description = Describable::get($Handler)->getDescription();
            } catch (InvalidRouteException $ex) {}

            $url = "<a href='{$infoPath}{$this->mHandlerIDs[$route]}#{$route}'>{$routes[$route]->getPrefix()}</a>";
            $destination = $routes[$route]->getDestination();
            $Table->renderRowStart();
            $Table->renderTD($num++,      0, 'table-field-num');
            $Table->renderTD($url,      0, 'table-field-required');
            $Table->renderTD($description,      0, 'table-field-name');
            $Table->renderTD($destination,      0, 'table-field-description');
        }

        $Table->renderFooterStart();
        $Table->renderDataStart(5, 'table-field-footer-buttons', "style='text-align: left'");
        $Table->renderEnd();
    }

    /**
     * Render the header
     * @param IRequest $Request the IRequest instance for this render
     * @return void
     */
    protected function renderBodyHeaderContent(IRequest $Request)
    {
        $this->processRequest($Request);
        if($this->mPageLayout) {
            $this->mPageLayout->renderBodyHeaderContent($Request);
            return;
        }

        $HandlerRoute = $Request->getRoute();
        $handlerRoute = $HandlerRoute->getPrefix();
        echo RI::get()->ni(), "<h1>{$handlerRoute}</h1>";
    }

    /**
     * Render the navigation bar content
     * @param IRequest $Request the IRequest instance for this render
     * @return void
     */
    protected function renderNavBarContent(IRequest $Request)
    {

        $routes = $this->mHandlers->getAllRoutes(new RouteBuilder());
        //$basePath = Base::getClassPublicPath($this);
        list(,$infoPath) = explode(' ', $Request->getRoute()->getPrefix(), 2);
        $infoPath = substr(Config::getDomainPath(), 0, -1) . $infoPath .'/';

        foreach($this->mHandlers as $route => $Handler) {
            $description = "No Description";
            try{
                $description = Describable::get($Handler)->getDescription();
            } catch (InvalidRouteException $ex) {}
            $this->renderNavBarEntry("{$infoPath}{$this->mHandlerIDs[$route]}#{$route}", $description, $routes[$route]->getPrefix());
        }
    }

    /**
     * Render the header
     * @param IRequest $Request the IRequest instance for this render
     * @return void
     */
    protected function renderBodyFooterContent(IRequest $Request)
    {
        $this->processRequest($Request);
        if($this->mPageLayout) {
            $this->mPageLayout->renderBodyFooterContent($Request);
            return;
        }

    }

    private $mRequestProcessed = false;
    private function processRequest(IRequest $Request)
    {
        if($this->mRequestProcessed)
            return;
        $this->mRequestProcessed = true;

        if(!$apiClass = $Request->getNextArg())
            throw new \InvalidArgumentException("No API Class passed to ".__CLASS__);

        $Source = new $apiClass;

        if($Source instanceof IHandlerAggregate) {
            $this->mHandlers = $Source->getAggregateHandler();
        } else {
            throw new \InvalidArgumentException($apiClass. " does not implement IHandlerAggregate");
        }
        if(!($this->mHandlers instanceof IHandlerSet)) {
            throw new \InvalidArgumentException(get_class($this->mHandlers). " is not an instance of IHandlerSet");
        }

        $this->mHandlerIDs = array();
        $num = 1;
        foreach($this->mHandlers as $route=>$Handler) {
            $this->mHandlerIDs[$route] = $num++;
        }

        if($arg = $Request->getNextArg()) {
            $route = array_search($arg, $this->mHandlerIDs);
            $API = $this->mHandlers->get($route);
            if(!$API instanceof IAPI)
                throw new InvalidRouteException("Destination for '{$arg}' does not implement IAPI");
            $routes = $this->mHandlers->getAllRoutes(new RouteBuilder());
            $APIRoute = $routes[$route];
            $this->mPageLayout = new APIView($API, $APIRoute, null, $this->getTheme());
            //$APIView->renderViewContent($Request);
            return;
        }
    }
}
