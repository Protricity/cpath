<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Model\DB;


use CPath\Handlers\API;
use CPath\Handlers\APIRequiredParam;
use CPath\Interfaces\IRequest;
use CPath\Interfaces\IResponse;
use CPath\Interfaces\InvalidAPIException;
use CPath\Model\DB\Interfaces\IWriteAccess;
use CPath\Model\Response;

class API_Patch extends API_Get {

    /**
     * Construct an instance of this API
     * @param string|array $alternateColumns a column or array of columns that may be used to search for Models.
     * Note: PRIMARY key is already included
     * Warning: Only the first result will be used if multiple Models are found
     * @param PDOModel|IWriteAccess $Model the user source object for this API
     * @throws InvalidAPIException if no PRIMARY key column or alternative columns are available
     */
    function __construct(PDOModel $Model, $alternateColumns=NULL) {
        parent::__construct($Model, $alternateColumns);

        $defFilter = $Model::DEFAULT_FILTER;
        foreach($Model::findColumns($Model::UPDATE ?: PDOColumn::FLAG_UPDATE) as $Column)
            /** @var PDOColumn $Column */
            $Column->addToAPI($this, false, NULL, NULL, $defFilter);
    }

    /**
     * Get the API Description
     * @return String description for this API
     */
    function getDescription() {
        return "Update a ".$this->getModel()->modelName();
    }

    /**
     * Execute this API Endpoint with the entire request.
     * This method must call processRequest to validate and process the request object.
     * @param IRequest $Request the IRequest instance for this render which contains the request and args
     * @return IResponse|mixed the api call response with data, message, and status
     * @throws ModelNotFoundException if the model was not found
     */
    function execute(IRequest $Request) {

        $UpdateModel = parent::execute($Request);

        $Policy = $this->getSecurityPolicy();

        $Policy->assertWriteAccess($UpdateModel, $Request, IWriteAccess::INTENT_PATCH);
        if($UpdateModel instanceof IWriteAccess)
            $UpdateModel->assertWriteAccess($UpdateModel, $Request, IWriteAccess::INTENT_PATCH);

        foreach($Request as $column => $value)
            if($value !== NULL)
                $UpdateModel->updateColumn($column, $value, false);

        $c = $UpdateModel->commitColumns();
        if(!$c)
            return new Response("No columns were updated for {$UpdateModel}.", true, $UpdateModel);
        return new Response("Updated {$c} Field(s) for {$UpdateModel}.", true, $UpdateModel);
    }
}
