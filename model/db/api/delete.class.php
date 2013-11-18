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
use CPath\Interfaces\IDescribable;
use CPath\Interfaces\IRequest;
use CPath\Interfaces\IResponse;
use CPath\Model\DB\Interfaces\IAPIGetCallbacks;
use CPath\Model\DB\Interfaces\IWriteAccess;
use CPath\Model\Response;

class API_Delete extends API_Get implements IAPIGetCallbacks {

    /**
     * Get the Object Description
     * @return IDescribable|String a describable Object, or string describing this object
     */
    function getDescribable() {
        return "Delete a ".$this->getModel()->modelName();
    }

    /**
     * Add or modify fields of an API.
     * Note: Leave empty if unused.
     * @param Array &$fields the existing API fields to modify
     * @return IField[]|NULL return an array of prepared fields to use or NULL to ignore.
     */
    function prepareGetFields(Array &$fields) {
    }

    /**
     * Perform on successful API_Get execution
     * @param PDOModel $Model the returned model
     * @param IRequest $Request
     * @param IResponse $Response
     * @return IResponse|void
     */
    function onGetExecute(PDOModel $Model, IRequest $Request, IResponse $Response) {
        foreach($this->getHandlers() as $Handler)
            if($Handler instanceof IWriteAccess)
                $Handler->assertWriteAccess($Model, $Request, IWriteAccess::INTENT_DELETE);

        $Model::removeModel($Model);

        return new Response("Removed {$Model}", true, $Model);
    }
}
