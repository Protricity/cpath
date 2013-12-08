<?php
namespace CPath\Handlers\Views;

use CPath\Base;
use CPath\Builders\RouteBuilder;
use CPath\Config;
use CPath\Handlers\Api\Interfaces\IAPI;
use CPath\Handlers\InvalidRouteException;
use CPath\Handlers\Layouts\NavBarLayout;
use CPath\Handlers\Layouts\PageLayout;
use CPath\Handlers\Themes\CPathDefaultTheme;
use CPath\Handlers\Themes\Interfaces\ITheme;
use CPath\Helpers\Describable;
use CPath\Interfaces\IHandlerAggregate;
use CPath\Interfaces\IHandlerSet;
use CPath\Interfaces\ILogEntry;
use CPath\Interfaces\ILogListener;
use CPath\Interfaces\IRequest;
use CPath\Log;
use CPath\Misc\RenderIndents;

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

        RenderIndents::ni();
        ?>
    <ul class='field-table'>
        <li class='field-header clearfix'>
            <div class='field-num'>#</div>
            <div class='field-prefix'>Route</div>
            <div class='field-description'>Description</div>
            <div class='field-destination'>Destination</div>
        </li><?php foreach($this->mHandlers as $route => $Handler) {
            $description = "No Description";
            try{
                $description = Describable::get($Handler)->getDescription();
            } catch (InvalidRouteException $ex) {}
            echo "\n"; ?>
        <li class='field-item clearfix'>
            <div class='field-num'><?php echo $num++; ?>.</div>
            <div class='field-prefix'><a href='<?php echo $infoPath . $this->mHandlerIDs[$route]. '#' . $route; ?>'><?php echo $routes[$route]->getPrefix(); ?></a></div>
            <div class='field-description'><?php echo $description; ?></div>
            <div class='field-destination'><?php echo $routes[$route]->getDestination(); ?></div>
        </li><?php } echo "\n"; ?>
        <li class='field-footer clearfix'>
            <div class='field-num'></div>
            <div class='field-prefix'></div>
            <div class='field-description'></div>
            <div class='field-destination'></div>
        </li>
    </ul><?php
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
            $this->mPageLayout->renderBodyHeader($Request);
            return;
        }

        $HandlerRoute = $Request->getRoute();
        $handlerRoute = $HandlerRoute->getPrefix();
        echo "<h1>{$handlerRoute}</h1>";
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

        $num = 1;

        RenderIndents::ni();
        ?>
        <ul class='navbar-menu'>
        <?php foreach($this->mHandlers as $route => $Handler) {
            $description = "No Description";
            try{ move to layout
                $description = Describable::get($Handler)->getDescription();
            } catch (InvalidRouteException $ex) {}
            echo "\n"; ?>
            <li class='navbar-menu-item clearfix'>
            <?php echo $num++; ?>. <a href='<?php echo $infoPath . $this->mHandlerIDs[$route]. '#' . $route; ?>'><?php echo $routes[$route]->getPrefix(); ?></a>
            </li><?php } echo "\n"; ?>
        </ul><?php
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
            $this->mPageLayout = new APIView($this->getTheme(), $API, $APIRoute);
            //$APIView->renderViewContent($Request);
            return;
        }
    }
}
