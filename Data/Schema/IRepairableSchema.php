<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 12/22/2014
 * Time: 10:12 PM
 */
namespace CPath\Data\Schema;

interface IRepairableSchema
{
	/**
	 * Attempt to repair a writable schema
	 * @param IWritableSchema $DB
	 * @param \Exception $ex
	 */
	public function repairSchema(IWritableSchema $DB, \Exception $ex);
}