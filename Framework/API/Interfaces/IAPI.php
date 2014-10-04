<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Framework\API\Interfaces;

use CPath\Framework\API\Field\Collection\Interfaces\IFieldCollection;
use CPath\Framework\API\Field\Interfaces\IField;
use CPath\Request\IRequest;


interface IAPI { // extends IRender, IViewConfig,IDescribableAggregate, why?

    /**
     * Execute this API Endpoint with the entire request.
     * @param IRequest $Request the IRequest instance for this render which contains the request and args
     * @internal param Array $args additional arguments for this execution
     * @return \CPath\Response\IResponse the api call response with data, message, and status
     */
    function execute(IRequest $Request);

    /**
     * Get all API Fields
     * @param IRequest $Request the IRequest instance for this render which contains the request and args
     * @return IField[]|IFieldCollection
     */
    function getFields(IRequest $Request);
//
//    /**
//     * Process a request. Validates each Field. Provides optional Field formatting
//     * @param IRequest $Request the IRequest instance for this render which contains the request and args
//     * @return void
//     * @throws ValidationExceptions if one or more Fields fail to validate
//     */
//    public function processRequest(IRequest $Request);
}