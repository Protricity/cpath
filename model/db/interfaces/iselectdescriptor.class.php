<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Ari
 * Date: 8/8/13
 * Time: 11:11 PM
 * To change this template use File | Settings | File Templates.
 */
namespace CPath\Model\DB\Interfaces;

use CPath\Handlers\Api\Interfaces\IAPI;
use CPath\Model\DB\PDOColumn;
use CPath\Model\DB\PDOSelectLimitedStats;
use CPath\Model\DB\PDOSelectStats;


interface ISelectDescriptor {

    /**
     * Return the column for a query row value
     * @param String $columnName the name of the column to be translated
     * @return PDOColumn
     */
    function getColumn($columnName);

    /**
     * Return the API used for this query
     * @return IAPI
     */
    function getAPI();

    /**
     * @return PDOSelectLimitedStats
     */
    function getLimitedStats();

    /**
     * @param bool $allowCache
     * @return PDOSelectStats
     */
    function execFullStats($allowCache=true);
}