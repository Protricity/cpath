<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Model;
use CPath\Builders\RouteBuilder;
use CPath\Handlers\InvalidRouteException;
use CPath\Interfaces\IRoute;
use CPath\Util;

/**
 * Class Route - a route entry
 * @package CPath
 */
class FileRequestRoute implements IRoute{
    private $mRoutePath;
    public function __construct($routePrefixPath) {
        $this->mRoutePath = $routePrefixPath;
    }

    /**
     * Renders the route destination
     * @param array $request optional request parameters
     * @return void
     */
    public function render(Array $request=NULL) {
        header("HTTP/1.0 404 File request was passed to Script");
    }

    function match($requestPath) { throw new InvalidRouteException("File request was passed to Script"); }
    function getNextArg() { throw new InvalidRouteException("File request was passed to Script"); }
    function getPrefix() { throw new InvalidRouteException("File request was passed to Script"); }
    function getDestination() { throw new InvalidRouteException("File request was passed to Script"); }
    function setRequest(Array $request) { throw new InvalidRouteException("File request was passed to Script"); }
    function getRequest() { throw new InvalidRouteException("File request was passed to Script"); }
    function getHandler() { throw new InvalidRouteException("File request was passed to Script"); }

}