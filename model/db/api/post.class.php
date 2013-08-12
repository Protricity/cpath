<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Model\DB;


use CPath\Interfaces\IRequest;
use CPath\Interfaces\IResponse;
use CPath\Model\DB\Interfaces\IAssignAccess;
use CPath\Model\Response;

class API_Post extends API_Base {

    /**
     * Construct an instance of this API
     * @param PDOModel $Model the user source object for this API
     */
    function __construct(PDOModel $Model) {
        parent::__construct($Model);

        foreach($Model::findColumns($Model::INSERT ?: PDOColumn::FlagInsert) as $Column)
            $Column->addToAPI($this);
    }

    /**
     * Get the API Description
     * @return String description for this API
     */
    function getDescription() {
        return "Create a new ".$this->getModel()->modelName();
    }

    /**
     * Execute this API Endpoint with the entire request.
     * This method must call processRequest to validate and process the request object.
     * @param IRequest $Request the IRequest instance for this render which contains the request and args
     * @return IResponse|mixed the api call response with data, message, and status
     */
    function execute(IRequest $Request) {
        $Model = $this->getModel();
        $this->processRequest($Request);

        $Policy = $this->getSecurityPolicy();

        $Policy->assignAccessID($Request, IAssignAccess::INTENT_POST);
        if($Model instanceof IAssignAccess)
            $Model->assignAccessID($Request, IAssignAccess::INTENT_POST);

        $Model = $Model::createFromArray($Request);

        return new Response("Created " . $Model . " Successfully.", true, $Model);
    }
}
