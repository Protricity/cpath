<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/17/14
 * Time: 7:12 PM
 */
namespace CPath\Route;

use CPath\Request\IStaticRequestHandler;

class RouteCallback implements IRouteMap
{
    private $mCallback;

    public function __construct(\Closure $Callback) {
        $this->mCallback = $Callback;
    }

    /**
     * Map a Route prefix to a target class or instance. Return true if the route prefix was matched
     * @param String $prefix route prefix i.e. GET /my/path
     * @param IStaticRequestHandler|IRoutable|String $target Request handler class name or instance
     * @return bool if true the route prefix was matched
     */
    function route($prefix, $target) {
        $call = $this->mCallback;
        return call_user_func_array($call, func_get_args());
    }
}