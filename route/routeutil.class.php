<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Route;
use CPath\Config;
use CPath\Interfaces\IBuildable;
use CPath\Interfaces\IHandler;
use CPath\Interfaces\IRequest;

/**
 * Class Route - a route entry
 * @package CPath
 */
class RouteUtil implements IHandler{

    private $mRoute, $mMethod, $mPath;

    function __construct(IRoute $Route) {
        $this->mRoute = $Route;
        $prefix = $Route->getPrefix();

        if(strpos($prefix, ' ') === false)
            list($this->mMethod, $this->mPath) = explode(' ', $prefix);
        else
            $this->mMethod = $prefix;
    }

    function getMethod() { return $this->mMethod; }
    function getPath() { return $this->mPath; }

    function buildPublicURL($withDomain=true) {
        return ($withDomain ? Config::getDomainPath() : '')
            . $this->mPath;
    }

    /**
     * Renders the route destination using an IRequest instance
     * @param IRequest $Request the request to render
     * @return void
     */
    function render(IRequest $Request) {
        $Handler = $this->mRoute->loadHandler();
        $Handler->render($Request);
    }
}