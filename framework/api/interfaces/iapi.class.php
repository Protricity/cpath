<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Framework\Api\Interfaces;

use CPath\Framework\Request\Interfaces\IRequest;
use CPath\Framework\Response\Interfaces\IResponse;


interface IAPI { // extends IRender, IViewConfig,IDescribableAggregate, why?

    /**
     * Execute this API Endpoint with the entire request.
     * @param IRequest $Request the IRequest instance for this render which contains the request and args
     * @return IResponse the api call response with data, message, and status
     */
    function execute(IRequest $Request);

    /**
     * Get all API Fields
     * @return IField[]
     */
    function getFields();
//
//    /**
//     * Process a request. Validates each Field. Provides optional Field formatting
//     * @param IRequest $Request the IRequest instance for this render which contains the request and args
//     * @return void
//     * @throws ValidationExceptions if one or more Fields fail to validate
//     */
//    public function processRequest(IRequest $Request);
}