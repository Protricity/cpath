<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Model\DB;
use CPath\Base;
use CPath\Handlers\API;
use CPath\Handlers\APIField;
use CPath\Handlers\APIParam;
use CPath\Handlers\APIRequiredField;
use CPath\Handlers\APIRequiredParam;
use CPath\Handlers\HandlerSet;
use CPath\Handlers\InvalidRouteException;
use CPath\Handlers\SimpleAPI;
use CPath\Handlers\ValidationException;
use CPath\Interfaces\HandlerSetException;
use CPath\Interfaces\IAPI;
use CPath\Interfaces\IHandlerAggregate;
use CPath\Interfaces\IHandlerSet;
use CPath\Interfaces\IRoute;
use CPath\Interfaces\IJSON;
use CPath\Interfaces\IResponse;
use CPath\Interfaces\IResponseAggregate;
use CPath\Interfaces\IRoutable;
use CPath\Interfaces\IRouteBuilder;
use CPath\Interfaces\IXML;
use CPath\Log;
use CPath\Model\Response;

class InvalidAPIException extends \Exception {}

class PDOAPIHandlerSet extends HandlerSet {

    /**
     * Adds an IHandler to the set by route
     * @param String $route the route to the sub api i.e. POST (any POST), GET search (relative), GET /site/users/search (absolute)
     * @param IAPI $API the IAPI instance to add to the set
     * @param bool $replace if true, replace any existing handlers
     * @return IAPI the passed IAPI instance
     */
    public function addAPI($route, IAPI $API, $replace=false) {
        $this->addHandler($route, $API, $replace);
        return $API;
    }

    /**
     * Returns an IHandler instance by route
     * @param $route String the route associated with this handler
     * @return IAPI
     * @throws InvalidRouteException if the route is not found
     * @throws InvalidAPIException if the route is not an IAPI instance
     */
    public function getAPI($route) {
        $API = $this->getHandler($route);
        if(!($API instanceof IAPI))
            throw new InvalidAPIException("Route '{$route}' does not contain a valid IAPI instance");
        return $API;
    }
}
