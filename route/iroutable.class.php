<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Route;

use CPath\Interfaces\IBuildable;
use CPath\Framework\Render\Interfaces\IRender;

interface IRoutable extends IRender, IBuildable {

    /**
     * Returns the route for this IRender
     * @return IRoute|RoutableSet a new IRoute (typically a RouteableSet) instance
     */
    function loadRoute();
}