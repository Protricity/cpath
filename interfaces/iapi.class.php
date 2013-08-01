<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Interfaces;

interface IAPI extends IHandler, IRoutable {

    /**
     * Execute this API Endpoint with the entire request.
     * @param array $request associative array of request Fields, usually $_GET or $_POST
     * @return \CPath\Interfaces\IResponse the api call response with data, message, and status
     */
    function execute(Array $request);

    /**
     * Set the route for this IAPI
     * @param IRoute $Route
     * @return void
     */
    function setRoute(IRoute $Route);

    /**
     * Get the route for this IAPI
     * @return IRoute
     */
    function getRoute();
}