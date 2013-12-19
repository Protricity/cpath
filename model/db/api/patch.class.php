<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Model\DB;


use CPath\Handlers\API;
use CPath\Handlers\Api\Interfaces\IField;
use CPath\Describable\IDescribable;
use CPath\Interfaces\IRequest;
use CPath\Interfaces\IResponse;
use CPath\Model\DB\Interfaces\IAPIGetCallbacks;
use CPath\Model\DB\Interfaces\IWriteAccess;
use CPath\Model\Response;

class API_Patch extends API_Get implements IAPIGetCallbacks {

    /**
     * Add or modify fields of an API.
     * Note: Leave empty if unused.
     * @param Array &$fields the existing API fields to modify
     * @return IField[]|NULL return an array of prepared fields to use or NULL to ignore.
     */
    function prepareGetFields(Array &$fields)
    {
        $Model = $this->getModel();

        $defFilter = $Model::DEFAULT_FILTER;
        foreach($Model::findColumns($Model::UPDATE ?: PDOColumn::FLAG_UPDATE) as $Column)
            /** @var PDOColumn $Column */
            if(!isset($fields[$Column->getName()]))
                $fields[$Column->getName()] = $Column->generateAPIField(false, NULL, NULL, $defFilter);
    }

    /**
     * Get the Object Description
     * @return IDescribable|String a describable Object, or string describing this object
     */
    function getDescribable() {
        return "Update a ".$this->getModel()->modelName();
    }

    /**
     * Perform on successful API_Get execution
     * @param PDOModel|PDOPrimaryKeyModel $UpdateModel the returned model
     * @param IRequest $Request
     * @param IResponse $Response
     * @return IResponse|void
     */
    final function onGetExecute(PDOModel $UpdateModel, IRequest $Request, IResponse $Response) {

        foreach($this->getHandlers() as $Handler)
            if($Handler instanceof IWriteAccess)
                $Handler->assertWriteAccess($UpdateModel, $Request, IWriteAccess::INTENT_PATCH);

        foreach($Request as $column => $value)
            if($value !== NULL)
                $UpdateModel->updateColumn($column, $value, false);

        $c = $UpdateModel->commitColumns();
        if(!$c)
            return new Response("No columns were updated for {$UpdateModel}.", true, $UpdateModel);
        return new Response("Updated {$c} Field(s) for {$UpdateModel}.", true, $UpdateModel);
    }
}
