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
use CPath\Model\DB\Interfaces\IAPIExecute;
use CPath\Model\DB\Interfaces\ILimitApiQuery;
use CPath\Model\DB\Interfaces\IWriteAccess;
use CPath\Model\Response;

class API_Delete extends API_Get implements IGetExecute {

    /**
     * Get the API Description
     * @return String description for this API
     */
    function getDescription() {
        return "Delete a ".$this->getModel()->modelName();
    }

    /**
     * Perform on successful API_Get execution
     * @param PDOModel $Model the returned model
     * @param IRequest $Request
     * @param IResponse $Response
     * @return IResponse|void
     */
    function onGetExecute(PDOModel $Model, IRequest $Request, IResponse $Response) {
        $this->getSecurityPolicy()->assertWriteAccess($Model, $Request, IWriteAccess::INTENT_DELETE);

        $Model::removeModel($Model);

        return new Response("Removed {$Model}", true, $Model);
    }
}
