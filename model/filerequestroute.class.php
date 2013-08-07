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
use CPath\Interfaces\IRequest;
use CPath\Interfaces\IRoute;
use CPath\Util;

/**
 * Class Route - a route entry
 * @package CPath
 */
class FileRequestRoute extends MissingRoute implements IRoute{
    private $mRoutePath;
    public function __construct($routePrefixPath) {
        $this->mRoutePath = $routePrefixPath;
    }

    /**
     * Renders the route destination
     * @param IRequest $Request
     * @return void
     */
    public function render(IRequest $Request) {
        header("HTTP/1.0 404 File request was passed to Script");
    }

}