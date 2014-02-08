<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Ari
 * Date: 8/8/13
 * Time: 11:11 PM
 * To change this template use File | Settings | File Templates.
 */
namespace CPath\Framework\PDO\Interfaces;

use CPath\Framework\PDO\Query\PDOWhere;
use CPath\Framework\Request\Interfaces\IRequest;

interface IAPIGetBrowseCallbacks {

    /**
     * Prepare a Search query
     * Use this interface call to constrain a query by adding WHERE statements
     * @param \CPath\Framework\PDO\Query\PDOWhere $Select the query statement to limit.
     * @param IRequest $Request The api request to process and or validate validate
     * @return void
     */
    function prepareQuery(PDOWhere $Select, IRequest $Request);
}