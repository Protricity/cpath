<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Route;

use CPath\Request\IStaticRequestHandler;

interface IRouteMap {

    /**
     * Map a Route prefix to a target class or instance. Return true if the route prefix was matched
     * @param String $prefix route prefix i.e. GET /my/path
     * @param IStaticRequestHandler|IRoutable|String $target Request handler class name or instance
     * @return bool if true the route prefix was matched
     */
    function route($prefix, $target);
}

