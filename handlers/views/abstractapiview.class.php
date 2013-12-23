<?php
namespace CPath\Handlers\Views;

use CPath\Base;
use CPath\Config;
use CPath\Handlers\Api\Interfaces\IAPI;
use CPath\Handlers\Layouts\NavBarLayout;
use CPath\Handlers\Themes\CPathDefaultTheme;
use CPath\Handlers\Themes\Interfaces\ITheme;
use CPath\Interfaces\ILogEntry;
use CPath\Interfaces\ILogListener;
use CPath\Interfaces\IRequest;
use CPath\Log;
use CPath\Route\RoutableSet;

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
