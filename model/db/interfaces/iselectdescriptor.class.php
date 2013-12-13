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
use CPath\Interfaces\IDescribable;


interface ISelectDescriptor {

    /**
     * Return the column title for a query row value
     * @param String $columnName the name of the column to be translated
     * @return IDescribable
     */
    function getColumnDescriptor($columnName);

    /**
     * Return the API used for this query
     * @return IAPI
     */
    function getAPI();
}