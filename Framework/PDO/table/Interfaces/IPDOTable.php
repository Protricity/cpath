<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Framework\PDO\Table\Interfaces;

use CPath\Framework\PDO\Table\Column\Collection\Types\PDOColumnCollection;

interface IPDOTable
{
    /**
     * @return \CPath\Framework\PDO\DB\PDODatabase
     */
    function getDB();


    /**
     * Returns the table name
     * @return string the model class name
     */
    function getTableName();

    /**
     * Returns the model class name
     * @return string the model class name
     */
    function getModelClass();

    /**
     * Returns the model name from comment or the class name
     * @return string the model name
     */
    function getModelName();

    /**
     * Return all table columns
     * @return PDOColumnCollection
     */
    function getColumns();

}