<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Route;
use CPath\Interfaces\IHandler;
use CPath\Interfaces\IRequest;

/**
 * Class MissingRoute - a placeholder for missing routes
 * @package CPath
 */
class MissingRoute implements IRoute{
    private $mRoutePath;
    public function __construct($routePath) {
        $this->mRoutePath = $routePath;
    }

    /**
     * Renders the route destination
     * @param IRequest $Request
     * @return void
     */
    public function renderDestination(IRequest $Request) {
        header("HTTP/1.0 404 Route not found");
        print("No Routes Matched: " . $this->mRoutePath);
    }

    function getHandler() { throw new NoRoutesFoundException("Route '{$this->mRoutePath}' was not found"); }
    function match($requestPath) { throw new NoRoutesFoundException("Route '{$this->mRoutePath}' was not found"); }
    function getPrefix() { throw new NoRoutesFoundException("Route '{$this->mRoutePath}' was not found"); }
    function getDestination() { throw new NoRoutesFoundException("Route '{$this->mRoutePath}' was not found"); }
    function getNextArg() { throw new NoRoutesFoundException("Route '{$this->mRoutePath}' was not found"); }
    function setRequest(Array $request) { throw new NoRoutesFoundException("Route '{$this->mRoutePath}' was not found"); }
    function getRequest() { throw new NoRoutesFoundException("Route '{$this->mRoutePath}' was not found"); }
    function getExportArgs() { throw new InvalidRouteException("File request was passed to Script"); }
    function exportConstructorArgs() { throw new InvalidRouteException("File request was passed to Script"); }
    function routeRequestToHandler(IRequest $Request) { throw new InvalidRouteException("File request was passed to Script"); }
    protected function &getArray() { throw new InvalidRouteException("File request was passed to Script"); }
}