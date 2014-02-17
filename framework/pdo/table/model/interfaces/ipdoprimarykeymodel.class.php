<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Framework\PDO\Table\Model\Interfaces;


interface IPDOPrimaryKeyModel extends IPDOModel {

    /**
     * UPDATE column values for this Model
     * @return int the number of columns updated
     * @throws \Exception if no primary key exists
     */
    function commitColumns();

    /**
     * UPDATE a column value for this Model
     * @param String $column the column name to update
     * @param String $value the value to set
     * @param bool $commit set true to commit now, otherwise use ->commitColumns
     * @return $this
     */
    function updateColumn($column, $value, $commit=true);

    /**
     * Load column values for an active instance
     * @param String $_columns a varargs of strings representing columns
     * @return Array an array of column values
     * @throws \Exception if there is no PRIMARY key for this table
     */
    function loadColumnValues($_columns);

    /**
     * Remove this instance from the database
     */
    function remove();
}


