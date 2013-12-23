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
use CPath\Response\IResponseCode;
use CPath\Response\CodedException;

/**
 * Class MissingRoute - a placeholder for missing routes
 * @package CPath
 */
class MissingRoute implements IRoute{
    private $mRoutePath;
    public function __construct($routePath) {
        $this->mRoutePath = $routePath;
    }

    public function loadHandler() {
        throw new CodedException("No Routes Matched " . $this->mRoutePath, IResponseCode::STATUS_NOT_FOUND);
    }


    function match($requestPath) { throw new NoRoutesFoundException("Route '{$this->mRoutePath}' was not found"); }
    function getPrefix() { throw new NoRoutesFoundException("Route '{$this->mRoutePath}' was not found"); }
    function getDestination() { throw new NoRoutesFoundException("Route '{$this->mRoutePath}' was not found"); }
    function exportConstructorArgs() { throw new InvalidRouteException("File request was passed to Script"); }
    protected function &getArray() { throw new InvalidRouteException("File request was passed to Script"); }

}