<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Framework\PDO;

use CPath\Describable\IDescribable;
use CPath\Framework\Api\Field\Interfaces\IField;
use CPath\Framework\PDO\Interfaces\IAPIGetCallbacks;
use CPath\Framework\PDO\Interfaces\IWriteAccess;
use CPath\Framework\PDO\Table\Column\Builders\Interfaces\IPDOColumnBuilder;

use CPath\Framework\PDO\Table\Column\Types\PDOColumn;
use CPath\Framework\PDO\Table\Model\Types\PDOPrimaryKeyModel;
use CPath\Framework\Request\Interfaces\IRequest;
use CPath\Framework\Response\Interfaces\IResponse;
use CPath\Framework\Response\Types\DataResponse;

class API_Patch extends API_Get implements IAPIGetCallbacks {

    /**
     * Add or modify fields of an API.
     * Note: Leave empty if unused.
     * @param Array &$fields the existing API fields to modify
     * @return \CPath\Framework\Api\Field\\CPath\Framework\Api\Field\Interfaces\IField[]|NULL return an array of prepared fields to use or NULL to ignore.
     */
    function prepareGetFields(Array &$fields)
    {
        $T = $this->getTable();

        $defFilter = $T::DEFAULT_FILTER;
        foreach($T->findColumns(PDOColumn::FLAG_UPDATE) as $Column)
            /** @var IPDOColumnBuilder $Column */
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
            return new DataResponse("No columns were updated for {$UpdateModel}.", true, $UpdateModel);
        return new DataResponse("Updated {$c} Field(s) for {$UpdateModel}.", true, $UpdateModel);
    }
}
