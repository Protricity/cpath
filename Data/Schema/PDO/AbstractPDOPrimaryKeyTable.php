<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 12/21/2014
 * Time: 10:44 PM
 */
namespace CPath\Data\Schema\PDO;

use CPath\Request\IRequest;

define('AbstractPDOPrimaryKeyTable', __NAMESPACE__ . '\\AbstractPDOPrimaryKeyTable');
abstract class AbstractPDOPrimaryKeyTable extends AbstractPDOTable
{
	const className = AbstractPDOPrimaryKeyTable;

	const PRIMARY_COLUMN = null;

//	function insertOrFetch(Array $insertData) {
//		try {
//			return $this->insertAndFetch($insertData);
//		} catch (DuplicateRowException $ex) {
//			return $this->fetch($insertData);
//		}
//		$id = $this->insertAndReturnRowID($insertData);
//		$fetch = $this->fetch($id, static::PRIMARY_COLUMN);
//		if(!$fetch)
//			throw new \Exception("Unable to fetch newly inserted ID: " . $id);
//		return $fetch;
//	}

	function insertOrUpdate($primaryKeyValue, Array $insertData=array()) {
		$Row = $this->fetch(static::PRIMARY_COLUMN, $primaryKeyValue);
		if($Row) {
			if(sizeof($insertData) > 0)
				$this->update($insertData)
					->where(static::PRIMARY_COLUMN, $primaryKeyValue);
		} else {
			$insertData[static::PRIMARY_COLUMN] = $primaryKeyValue;
			$id = $this->insertAndReturnRowID($insertData);
			$Row = $this->fetch(static::PRIMARY_COLUMN, $id);
		}
		return $Row;
	}

	function insertAndFetch(Array $insertData) {
		$id = $this->insertAndReturnRowID($insertData);
		$fetch = $this->fetch(static::PRIMARY_COLUMN, $id);
		if(!$fetch)
			throw new \Exception("Unable to fetch newly inserted ID: " . $id);
		return $fetch;
	}

	function insertAndReturnRowID(Array $insertData, IRequest $Request=null) {
		$this->insert($insertData)
			->execute($Request);
		if (isset($insertData[static::PRIMARY_COLUMN]))
			$id = $insertData[static::PRIMARY_COLUMN];
		else
			$id = $this->getDatabase()->lastInsertId();
		return $id;
	}
}