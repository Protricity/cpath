<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Route;

use CPath\Framework\Build\IBuildable;
use CPath\Framework\Render\IRender;

interface IRoutable extends IRender, IBuildable {

    /**
     * Returns the route for this IRender
     * @return IRoute|RoutableSet a new IRoute (typically a RouteableSet) instance
     */
    function loadRoute();
}