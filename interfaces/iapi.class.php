<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Interfaces;

interface IAPI extends IHandler {

    /**
     * Execute this API Endpoint with the entire request.
     * @param array $request associative array of request Fields, usually $_GET or $_POST
     * @return \CPath\Interfaces\IResponse the api call response with data, message, and status
     */
    function execute(Array $request);

    /**
     * Execute this API Endpoint with the entire request returning an IResponse object.
     * If an exception occurs, it should still return an IResponse object with an error code
     * @param array $request associative array of request Fields, usually $_GET or $_POST
     * @return IResponse the api call response with data, message, and status
     */
    public function executeAsResponse(Array $request);

    function getDisplayRoute(&$methods);
}