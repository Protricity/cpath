<?php
namespace CPath\Handlers\Views;

use CPath\Base;
use CPath\Config;
use CPath\Describable\Describable;
use CPath\Framework\Api\Interfaces\IAPI;
use CPath\Handlers\Layouts\NavBarLayout;
use CPath\Handlers\Themes\CPathDefaultTheme;
use CPath\Handlers\Themes\Interfaces\ITheme;
use CPath\Interfaces\ILogEntry;
use CPath\Interfaces\ILogListener;
use CPath\Framework\Request\Interfaces\IRequest;
use CPath\Log;
use CPath\Route\IRoute;
use CPath\Route\RoutableSet;
use CPath\Route\RoutableSetWrapper;
use CPath\Route\RouteUtil;

abstract class AbstractAPIView extends NavBarLayout implements ILogListener {

    const BUILD_IGNORE = true;
    private $mLog = array();

    public function __construct(ITheme $Theme=null) {
        parent::__construct($Theme ?: CPathDefaultTheme::get());

        if(Config::$Debug)
            Log::addCallback($this);
    }

    abstract protected function setupAPIHeadFields(IRequest $Request);

    function onLog(ILogEntry $log) {
        $this->mLog[] = $log;
    }

    /**
     * Set up <head> element fields for this View
     * @param IRequest $Request
     */
    final protected function setupHeadFields(IRequest $Request) {
        $basePath = Base::getClassPublicPath($this, false);
        //$this->addHeadStyleSheet($basePath . 'assets/apiview.css');
        ///$this->addHeadScript($basePath . 'assets/apiview.js');
        $this->addHeadScript($basePath . 'assets/vkbeautify.min.js');

        $this->setupAPIHeadFields($Request);
    }

    protected function getRoutableSetIDs(RoutableSet $Routes) {
        $ids = array();
        $i = 1;
        /** @var IRoute $Route */
        foreach($Routes as $prefix => $Route) {
            $Handler = $Route->loadHandler();
            if($prefix[0] == '#')
                continue;
            if(!$Handler instanceof IAPI)
                continue;
            $ids[$i++] = $prefix;
        }
        return $ids;
    }


    /**
     * Render the navigation bar content
     * @param IRequest $Request the IRequest instance for this render
     * @return void
     */
    protected function renderNavBarContent(IRequest $Request) {
        if($Request instanceof RoutableSetWrapper) {
            $Routes = $Request->getRoutableSet();
            $Route = $Request->getRoute();
            $Util = new RouteUtil($Route);

            $ids = $this->getRoutableSetIDs($Routes);
            foreach($ids as $i=>$prefix) {
                /** @var IRoute $Route */
                $Route = $Routes[$prefix];
                $Handler = $Route->loadHandler();
                if(!$Handler instanceof IAPI)
                    continue;
                $Describable = Describable::get($Handler);
                $this->renderNavBarEntry($Util->buildPublicURL(false) . '/' . $i . '#' . $Route->getPrefix(), $Describable);
            }
        }
    }

    // Static

    /**
     * Returns the default IHandlerSet collection for this PDOModel type.
     * Note: if this method is called in a PDOModel thta does not implement IRoutable, a fatal error will occur
     * @param RoutableSet $Routes
     * @param IAPI $API
     * @param String $token
     * @return RoutableSet a set of common routes for this PDOModel type
     */
    static function addRoutes(RoutableSet $Routes, IAPI $API, $token=':api') {
        $Routes['GET ' . $token] = new static($API);
        $Routes['POST ' . $token] = new static($API);
        return $Routes;
    }
}
