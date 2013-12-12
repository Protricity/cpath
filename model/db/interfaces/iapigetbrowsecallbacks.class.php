<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Ari
 * Date: 8/8/13
 * Time: 11:11 PM
 * To change this template use File | Settings | File Templates.
 */
namespace CPath\Model\DB\Interfaces;

use CPath\Handlers\Api\Interfaces\IField;
use CPath\Interfaces\IRequest;
use CPath\Interfaces\IResponse;
use CPath\Model\DB\PDOModel;
use CPath\Model\DB\PDOWhere;

interface IAPIGetBrowseCallbacks {

    /**
     * Prepare a Search query
     * Use this interface call to constrain a query by adding WHERE statements
     * @param PDOWhere $Select the query statement to limit.
     * @param IRequest $Request The api request to process and or validate validate
     * @return void
     */
    function prepareQuery(PDOWhere $Select, IRequest $Request);
}