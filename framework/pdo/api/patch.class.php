<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Framework\PDO;

use CPath\Describable\IDescribable;
use CPath\Framework\PDO\Columns\PDOColumn;
use CPath\Framework\PDO\Interfaces\IAPIGetCallbacks;
use CPath\Framework\PDO\Interfaces\IWriteAccess;
use CPath\Framework\PDO\Model\PDOPrimaryKeyModel;
use CPath\Handlers\API;
use CPath\Handlers\Api\Interfaces\IField;
use CPath\Interfaces\IRequest;
use CPath\Response\IResponse;
use CPath\Response\Response;

class API_Patch extends API_Get implements IAPIGetCallbacks {

    /**
     * Add or modify fields of an API.
     * Note: Leave empty if unused.
     * @param Array &$fields the existing API fields to modify
     * @return IField[]|NULL return an array of prepared fields to use or NULL to ignore.
     */
    function prepareGetFields(Array &$fields)
    {
        $T = $this->getTable();

        $defFilter = $T::DEFAULT_FILTER;
        foreach($T->findColumns($T::UPDATE ?: PDOColumn::FLAG_UPDATE) as $Column)
            /** @var PDOColumn $Column */
            if(!isset($fields[$Column->getName()]))
                $fields[$Column->getName()] = $Column->generateAPIField(false, NULL, NULL, $defFilter);
    }

    /**
     * Get the Object Description
     * @return IDescribable|String a describable Object, or string describing this object
     */
    function getDescribable() {
        return "Update a ".$this->getTable()->getModelName();
    }

    /**
     * Perform on successful API_Get execution
     * @param PDOPrimaryKeyModel $UpdateModel the returned model
     * @param IRequest $Request
     * @param IResponse $Response
     * @return IResponse|void
     */
    final function onGetExecute(PDOPrimaryKeyModel $UpdateModel, IRequest $Request, IResponse $Response) {

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
