<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Route;

use CPath\Interfaces\IBuildable;
use CPath\Interfaces\IHandler;

interface IRoutable extends IHandler, IBuildable {

    /**
     * Returns the route for this IHandler
     * @return IRoute
     */
    function loadRoute();
}