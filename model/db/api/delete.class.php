<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Model\DB;


use CPath\Handlers\API;
use CPath\Handlers\APIField;
use CPath\Handlers\APIRequiredField;
use CPath\Interfaces\IRequest;
use CPath\Interfaces\IResponse;
use CPath\Model\DB\Interfaces\ILimitApiQuery;
use CPath\Model\DB\Interfaces\IWriteAccess;
use CPath\Model\Response;

class API_Delete extends API_Get {

    /**
     * Get the API Description
     * @return String description for this API
     */
    function getDescription() {
        return "Delete a ".$this->getModel()->modelName();
    }

    /**
     * Execute this API Endpoint with the entire request.
     * This method must call processRequest to validate and process the request object.
     * @param IRequest $Request the IRequest instance for this render which contains the request and args
     * @return IResponse|mixed the api call response with data, message, and status
     * @throws ModelNotFoundException if the model was not found
     */
    function execute(IRequest $Request) {

        $DeleteModel = parent::execute($Request);

        $this->getSecurityPolicy()->assertWriteAccess($DeleteModel, $Request, IWriteAccess::INTENT_DELETE);

        $DeleteModel::removeModel($DeleteModel);

        return new Response("Removed {$DeleteModel}", true, $DeleteModel);
    }
}
