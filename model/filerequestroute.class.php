<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Model;
use CPath\Interfaces\IRequest;
use CPath\Response\CodedException;
use CPath\Response\IResponseCode;
use CPath\Route\InvalidRouteException;
use CPath\Route\IRoute;
use CPath\Route\MissingRoute;
use CPath\Route\NoRoutesFoundException;

/**
 * Class Route - a route entry
 * @package CPath
 */
class FileRequestRoute extends MissingRoute implements IRoute{
    private $mRoutePath;
    public function __construct($routePrefixPath) {
        $this->mRoutePath = $routePrefixPath;
    }


    public function loadHandler() {
        throw new CodedException("File request was passed to Script", IResponseCode::STATUS_NOT_FOUND);
    }


    function getPrefix() { throw new NoRoutesFoundException("Route '{$this->mRoutePath}' was not found"); }
    function getDestination() { throw new NoRoutesFoundException("Route '{$this->mRoutePath}' was not found"); }
    function exportConstructorArgs() { throw new InvalidRouteException("File request was passed to Script"); }
    protected function &getArray() { throw new InvalidRouteException("File request was passed to Script"); }
}