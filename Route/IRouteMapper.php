<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Route;

interface IRouteMapper {

    /**
     * Map a Route prefix to a target class or inst. Return true if the route prefix was matched
     * @param String $prefix route prefix i.e. GET /my/path
     * @param IRoutable|IRouteMap|String $target Request handler class name or inst
     * @param null $_arg Additional varargs
     * @return bool if true the rendering has occurred
     */
    function route($prefix, $target, $_arg=null);
}

