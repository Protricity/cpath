<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/11/14
 * Time: 9:47 PM
 */
namespace CPath\Data\Schema;

interface IWritableSchema
{
	/**
	 * Create a table in the schema
	 * @param IReadableSchema $Schema
	 * @param String $tableName
	 * @param String|null $tableArgs
	 * @param String|null $tableComment
	 * @return void
	 */
    function writeTable(IReadableSchema $Schema, $tableName, $tableArgs = null, $tableComment = null);

	/**
	 * Write a column to the last schema table
	 * @param IReadableSchema $Schema
	 * @param String $columnName
	 * @param String|null $columnArgs
	 * @param String|null $columnComment
	 * @return void
	 */
    function writeColumn(IReadableSchema $Schema, $columnName, $columnArgs = null, $columnComment = null);

	/**
	 * Write a column index to the last schema table
	 * @param IReadableSchema $Schema
	 * @param $indexName
	 * @param String $columns list of columns comma delimited
	 * @param String|null $indexArgs
	 * @param String|null $indexComment
	 * @return mixed
	 */
    function writeIndex(IReadableSchema $Schema, $indexName, $columns, $indexArgs = null, $indexComment = null);
}
define('IWritableSchema', __NAMESPACE__ . '\\IWritableSchema');
