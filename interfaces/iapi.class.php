<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Interfaces;

use CPath\Handlers\IAPIField;

interface IAPI extends IHandler, IRoutable {

    /**
     * Execute this API Endpoint with the entire request.
     * @param IRoute $Route the IRoute instance for this render which contains the request and args
     * @return \CPath\Interfaces\IResponse the api call response with data, message, and status
     */
    function execute(IRoute $Route);

    /**
     * Get all API Fields
     * @return IAPIField[]
     */
    function getFields();
}