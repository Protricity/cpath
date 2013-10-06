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

class API_Patch extends API_Get implements IGetExecute {

    /**
     * Set up API fields. Lazy-loaded when fields are accessed
     * @return void
     * @throws InvalidAPIException if no PRIMARY key column or alternative columns are available
     */
    function setupAPI() {
        parent::setupAPI();
        $Model = $this->getModel();

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
     * Perform on successful API_Get execution
     * @param PDOModel $UpdateModel the returned model
     * @param IRequest $Request
     * @param IResponse $Response
     * @return IResponse|void
     */
    final function onGetExecute(PDOModel $UpdateModel, IRequest $Request, IResponse $Response) {

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
