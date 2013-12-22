<?php
namespace CPath\Handlers\Views;

use CPath\Actions\IActionManager;
use CPath\Base;
use CPath\Config;
use CPath\Handlers\API\Fragments\APIDebugFormFragment;
use CPath\Handlers\API\Fragments\APIResponseBoxFragment;
use CPath\Handlers\Api\Interfaces\IAPI;
use CPath\Handlers\Api\Interfaces\IParam;
use CPath\Handlers\Layouts\NavBarLayout;
use CPath\Handlers\Themes\CPathDefaultTheme;
use CPath\Handlers\Themes\Interfaces\ITheme;
use CPath\Handlers\Themes\Util\SearchFormUtil;
use CPath\Handlers\Themes\Util\TableThemeUtil;
use CPath\Describable\Describable;
use CPath\Interfaces\ILogEntry;
use CPath\Interfaces\ILogListener;
use CPath\Interfaces\IRequest;
use CPath\Interfaces\IResponse;
use CPath\Interfaces\IViewConfig;
use CPath\Log;
use CPath\Misc\RenderIndents as RI;
use CPath\Model\ExceptionResponse;
use CPath\Route\IRoute;
use CPath\Route\RouteSet;
use CPath\Util;

class APIView extends NavBarLayout implements ILogListener {

    const BUILD_IGNORE = true;
    private $mLog = array();
    private $mAPI = null;
    private $mResponse = null;
    private $mRoute;
    private $mForm;
    private $mResponseBox;

    public function __construct(IAPI $API, IRoute $Route=null, IResponse $Response=null, ITheme $Theme=null) {
        $this->mAPI = $API;
        $this->mResponse = $Response;
        $this->mRoute = $Route;
        parent::__construct($Theme ?: CPathDefaultTheme::get());

        $this->mForm = new APIDebugFormFragment($this->mAPI);

        $this->mResponseBox = new APIResponseBoxFragment($this->getTheme());

        if(Config::$Debug)
            Log::addCallback($this);
    }

    function onLog(ILogEntry $log) {
        $this->mLog[] = $log;
    }

    /**
     * Set up <head> element fields for this View
     * @param IRequest $Request
     */
    protected function setupHeadFields(IRequest $Request) {
        $basePath = Base::getClassPublicPath($this, false);
        //$this->addHeadStyleSheet($basePath . 'assets/apiview.css');
        ///$this->addHeadScript($basePath . 'assets/apiview.js');
        $this->addHeadScript($basePath . 'assets/vkbeautify.min.js');
        $this->mAPI->addHeadElementsToView($this);

        $this->mForm->addHeadElementsToView($this);

        if($this->mResponseBox instanceof IViewConfig)
            $this->mResponseBox->addHeadElementsToView($this);

        //$this->addHeadFields($Request);
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
        $API = $this->mAPI;
        $Route = $this->mRoute ?: $Request->getRoute();
        $route = $Route->getPrefix();
        echo RI::ni(), "<h1>{$route}</h1>";
        echo RI::ni(), "<h2>", Describable::get($API)->getDescription(), "</h2>";
    }

    /**
     * Render the navigation bar content
     * @param IRequest $Request the IRequest instance for this render
     * @return void
     */
    protected function renderNavBarContent(IRequest $Request)
    {
        // TODO: Implement renderNavBarContent() method.
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

    // Static

    /**
     * Returns the default IHandlerSet collection for this PDOModel type.
     * Note: if this method is called in a PDOModel thta does not implement IRoutable, a fatal error will occur
     * @param RouteSet $Routes
     * @param IAPI $API
     * @param String $token
     * @return RouteSet a set of common routes for this PDOModel type
     */
    static function addRoutes(RouteSet $Routes, IAPI $API, $token=':api') {
        $Routes['GET ' . $token] = new static($API);
        $Routes['POST ' . $token] = new static($API);
        return $Routes;
    }
}
