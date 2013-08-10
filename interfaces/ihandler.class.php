<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Interfaces;


interface IHandler {
    //const ROUTE_METHODS = 'GET|POST|CLI';   // Default accepted methods are GET and POST
    //const ROUTE_PATH = NULL;                // No custom route path. Path is based on namespace + class name

    /**
     * Render this handler
     * @param IRequest $Request the IRequest instance for this render
     * @return void
     */
    function render(IRequest $Request);
}